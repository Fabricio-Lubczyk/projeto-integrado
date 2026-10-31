<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminGameUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return [
            'title' => 'Jogo atualizado',
            'developer' => 'Novo estudio',
            'released_on' => '2020-03-15',
            'description' => 'Uma nova descricao para o jogo.',
            ...$extra,
        ];
    }

    public function test_guests_and_regular_users_cannot_edit_games(): void
    {
        $game = Game::factory()->create(['title' => 'Jogo original']);

        $this->get(route('admin.games.edit', $game))->assertRedirect(route('login'));
        $this->put(route('admin.games.update', $game), $this->payload())->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.games.edit', $game))->assertForbidden();
        $this->put(route('admin.games.update', $game), $this->payload())->assertForbidden();

        $this->assertDatabaseCount('games', 1);
        $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => 'Jogo original']);
    }

    public function test_edit_form_contains_current_values_and_relationship_selections(): void
    {
        $game = Game::factory()->create([
            'title' => 'Jogo original',
            'developer' => 'Estudio original',
            'released_on' => '2018-06-23',
            'description' => 'Descricao original do jogo.',
        ]);
        $genre = Genre::create(['name' => 'Aventura']);
        $platform = Platform::create(['name' => 'PC']);
        $otherGenre = Genre::create(['name' => 'RPG']);
        $otherPlatform = Platform::create(['name' => 'Xbox']);
        $game->genres()->attach($genre);
        $game->platforms()->attach($platform);

        $html = $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.games.edit', $game))->assertOk()
            ->assertSee('action="'.route('admin.games.update', $game).'"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('value="Jogo original"', false)
            ->assertSee('value="Estudio original"', false)
            ->assertSee('value="2018-06-23"', false)
            ->assertSee('Descricao original do jogo.')
            ->getContent();

        $this->assertChecked($html, 'genres', $genre->id);
        $this->assertChecked($html, 'platforms', $platform->id);
        $this->assertNotChecked($html, 'genres', $otherGenre->id);
        $this->assertNotChecked($html, 'platforms', $otherPlatform->id);
    }

    public function test_admin_can_update_all_fields_and_keep_the_same_title(): void
    {
        $game = Game::factory()->create(['title' => 'Mesmo titulo']);
        $this->actingAs(User::factory()->administrator()->create());

        $this->put(route('admin.games.update', $game), $this->payload([
            'title' => '  Mesmo titulo  ',
            'developer' => '  Novo estudio  ',
            'description' => '  Uma nova descricao para o jogo.  ',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.games.index'))->assertSessionHas('status');

        $this->assertDatabaseCount('games', 1);
        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'title' => 'Mesmo titulo',
            'developer' => 'Novo estudio',
            'description' => 'Uma nova descricao para o jogo.',
        ]);
        $this->assertSame('2020-03-15', $game->fresh()->released_on->format('Y-m-d'));
    }

    public function test_admin_can_change_title_and_clear_the_optional_release_date(): void
    {
        $game = Game::factory()->create(['title' => 'Titulo antigo', 'released_on' => '2017-02-24']);

        $this->actingAs(User::factory()->administrator()->create())
            ->put(route('admin.games.update', $game), $this->payload(['released_on' => '']))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.games.index'));

        $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => 'Jogo atualizado', 'released_on' => null]);
    }

    public function test_invalid_fields_and_another_games_title_do_not_change_the_game(): void
    {
        $game = Game::factory()->create(['title' => 'Jogo original', 'description' => 'Descricao original.']);
        Game::factory()->create(['title' => 'Titulo ocupado']);
        $this->actingAs(User::factory()->administrator()->create());

        $this->put(route('admin.games.update', $game), $this->payload([
            'title' => 'Titulo ocupado', 'developer' => '', 'released_on' => '2026-02-30', 'description' => '',
        ]))->assertSessionHasErrors(['title', 'developer', 'released_on', 'description']);

        $this->put(route('admin.games.update', $game), $this->payload([
            'title' => str_repeat('a', 151), 'developer' => str_repeat('a', 121), 'description' => str_repeat('a', 5001),
        ]))->assertSessionHasErrors(['title', 'developer', 'description']);

        $this->put(route('admin.games.update', $game), $this->payload([
            'title' => '   ', 'developer' => ['valor invalido'], 'released_on' => '15/03/2020',
        ]))->assertSessionHasErrors(['title', 'developer', 'released_on']);

        $this->assertDatabaseCount('games', 2);
        $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => 'Jogo original', 'description' => 'Descricao original.']);
    }

    public function test_update_replaces_relationships_and_missing_checkboxes_remove_all_links(): void
    {
        $game = Game::factory()->create([
            'title' => 'Titulo antigo',
            'developer' => 'Estudio antigo',
            'description' => 'Descricao antiga do jogo.',
        ]);
        $oldGenre = Genre::create(['name' => 'Aventura']);
        $newGenre = Genre::create(['name' => 'RPG']);
        $oldPlatform = Platform::create(['name' => 'PC']);
        $newPlatform = Platform::create(['name' => 'Xbox']);
        $game->genres()->attach($oldGenre);
        $game->platforms()->attach($oldPlatform);
        $this->actingAs(User::factory()->administrator()->create());

        $this->put(route('admin.games.update', $game), $this->payload([
            'genres' => [$newGenre->id], 'platforms' => [$newPlatform->id],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.games.index'));

        $this->assertSame([$newGenre->id], $game->fresh()->genres->modelKeys());
        $this->assertSame([$newPlatform->id], $game->fresh()->platforms->modelKeys());

        $this->get(route('games.index'))->assertOk()
            ->assertSee('Jogo atualizado')->assertSee('Novo estudio')
            ->assertSee('<li>RPG</li>', false)
            ->assertDontSee('Titulo antigo')->assertDontSee('Estudio antigo')
            ->assertDontSee('<li>Aventura</li>', false);
        $this->get(route('games.show', $game))->assertOk()
            ->assertSee('Jogo atualizado')->assertSee('Novo estudio')
            ->assertSee('Uma nova descricao para o jogo.')
            ->assertSee('<dd>RPG</dd>', false)->assertSee('<dd>Xbox</dd>', false)
            ->assertDontSee('Titulo antigo')->assertDontSee('Estudio antigo')
            ->assertDontSee('Descricao antiga do jogo.')
            ->assertDontSee('<dd>Aventura</dd>', false)->assertDontSee('<dd>PC</dd>', false);
        $this->get(route('games.index', ['genre' => $newGenre->id, 'platform' => $newPlatform->id]))
            ->assertOk()->assertSee('Jogo atualizado')
            ->assertViewHas('games', fn ($games) => $games->total() === 1);
        $this->get(route('games.index', ['genre' => $oldGenre->id]))
            ->assertOk()->assertDontSee('Jogo atualizado')
            ->assertViewHas('games', fn ($games) => $games->total() === 0);
        $this->get(route('games.index', ['platform' => $oldPlatform->id]))
            ->assertOk()->assertDontSee('Jogo atualizado')
            ->assertViewHas('games', fn ($games) => $games->total() === 0);

        $this->put(route('admin.games.update', $game), $this->payload())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.games.index'));

        $this->assertDatabaseCount('game_genre', 0);
        $this->assertDatabaseCount('game_platform', 0);
        $this->assertDatabaseCount('genres', 2);
        $this->assertDatabaseCount('platforms', 2);
    }

    public function test_invalid_relationships_are_rejected_without_changing_existing_links(): void
    {
        $game = Game::factory()->create(['title' => 'Jogo original']);
        $genre = Genre::create(['name' => 'Aventura']);
        $platform = Platform::create(['name' => 'PC']);
        $game->genres()->attach($genre);
        $game->platforms()->attach($platform);
        $this->actingAs(User::factory()->administrator()->create());

        foreach ([['bad'], [99999], [null]] as $ids) {
            $this->put(route('admin.games.update', $game), $this->payload(['genres' => $ids, 'platforms' => $ids]))
                ->assertSessionHasErrors(['genres.0', 'platforms.0']);
        }
        $this->put(route('admin.games.update', $game), $this->payload([
            'genres' => [$genre->id, $genre->id], 'platforms' => [$platform->id, $platform->id],
        ]))->assertSessionHasErrors(['genres.0', 'platforms.0']);
        $this->put(route('admin.games.update', $game), $this->payload(['genres' => 'bad', 'platforms' => 'bad']))
            ->assertSessionHasErrors(['genres', 'platforms']);

        $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => 'Jogo original']);
        $this->assertSame([$genre->id], $game->fresh()->genres->modelKeys());
        $this->assertSame([$platform->id], $game->fresh()->platforms->modelKeys());
    }

    public function test_relationship_storage_failure_rolls_back_attributes_and_all_links(): void
    {
        $game = Game::factory()->create(['title' => 'Jogo original', 'description' => 'Descricao original.']);
        $oldGenre = Genre::create(['name' => 'Aventura']);
        $newGenre = Genre::create(['name' => 'RPG']);
        $oldPlatform = Platform::create(['name' => 'PC']);
        $newPlatform = Platform::create(['name' => 'Xbox']);
        $game->genres()->attach($oldGenre);
        $game->platforms()->attach($oldPlatform);
        $this->actingAs(User::factory()->administrator()->create());
        DB::statement("CREATE TRIGGER fail_platform_update BEFORE INSERT ON game_platform BEGIN SELECT RAISE(FAIL, 'Falha simulada no vinculo'); END");
        $this->withoutExceptionHandling();

        try {
            $this->put(route('admin.games.update', $game), $this->payload([
                'genres' => [$newGenre->id], 'platforms' => [$newPlatform->id],
            ]));
            $this->fail('A edicao deveria falhar ao salvar a plataforma.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('Falha simulada no vinculo', $exception->getMessage());
        } finally {
            DB::statement('DROP TRIGGER fail_platform_update');
        }

        $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => 'Jogo original', 'description' => 'Descricao original.']);
        $this->assertSame([$oldGenre->id], $game->fresh()->genres->modelKeys());
        $this->assertSame([$oldPlatform->id], $game->fresh()->platforms->modelKeys());
        $this->assertDatabaseCount('game_genre', 1);
        $this->assertDatabaseCount('game_platform', 1);
    }

    public function test_missing_game_returns_not_found_for_edit_and_update(): void
    {
        $this->actingAs(User::factory()->administrator()->create());

        $this->get(route('admin.games.edit', 99999))->assertNotFound();
        $this->put(route('admin.games.update', 99999), $this->payload())->assertNotFound();

        $this->assertDatabaseCount('games', 0);
    }

    public function test_update_requires_a_valid_csrf_token_with_normal_middleware(): void
    {
        $game = Game::factory()->create(['title' => 'Jogo original']);
        $this->actingAs(User::factory()->administrator()->create());
        $environment = $this->app->environment();
        $this->app->instance('env', 'local');

        try {
            $this->withSession(['_token' => 'token-da-sessao'])
                ->put(route('admin.games.update', $game), $this->payload())->assertStatus(419);
            $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => 'Jogo original']);

            $this->withSession(['_token' => 'token-da-sessao'])
                ->put(route('admin.games.update', $game), $this->payload(['_token' => 'token-da-sessao']))
                ->assertSessionHasNoErrors()->assertRedirect(route('admin.games.index'));
        } finally {
            $this->app->instance('env', $environment);
        }

        $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => 'Jogo atualizado']);
    }

    private function assertChecked(string $html, string $field, int $id): void
    {
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*name="'.$field.'\[\]")(?=[^>]*value="'.$id.'")(?=[^>]*\bchecked\b)[^>]*>/',
            $html,
        );
    }

    private function assertNotChecked(string $html, string $field, int $id): void
    {
        $this->assertMatchesRegularExpression('/<input(?=[^>]*name="'.$field.'\[\]")(?=[^>]*value="'.$id.'")[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/<input(?=[^>]*name="'.$field.'\[\]")(?=[^>]*value="'.$id.'")(?=[^>]*\bchecked\b)[^>]*>/',
            $html,
        );
    }
}
