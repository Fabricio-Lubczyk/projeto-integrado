<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\User;
use Database\Seeders\GenreSeeder;
use Database\Seeders\PlatformSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GameTaxonomyTest extends TestCase
{
    use RefreshDatabase;

    private function taxonomy(): array
    {
        return [Genre::create(['name' => 'Ação']), Platform::create(['name' => 'PC'])];
    }

    private function payload(array $extra = []): array
    {
        return [...['title' => 'Jogo novo', 'developer' => 'Estúdio', 'description' => 'Uma aventura.'], ...$extra];
    }

    public function test_seeders_are_repeatable(): void
    {
        $this->seed([GenreSeeder::class, PlatformSeeder::class]);
        $this->seed([GenreSeeder::class, PlatformSeeder::class]);
        $this->seed();
        $this->seed();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('genres', 5);
        $this->assertDatabaseCount('platforms', 4);
    }

    public function test_admin_saves_multiple_relationships_and_public_pages_display_them(): void
    {
        [$genre, $platform] = $this->taxonomy();
        $otherGenre = Genre::create(['name' => 'RPG']);
        $otherPlatform = Platform::create(['name' => 'Xbox']);
        $this->actingAs(User::factory()->administrator()->create());
        $this->get(route('admin.games.create'))->assertOk()->assertSee('Ação')->assertSee('Xbox');
        $this->post(route('admin.games.store'), $this->payload([
            'genres' => [$genre->id, $otherGenre->id], 'platforms' => [$platform->id, $otherPlatform->id],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.games.create'));
        $game = Game::sole();
        $this->assertCount(2, $game->genres);
        $this->assertCount(2, $game->platforms);
        $this->assertTrue($genre->games->contains($game));
        $this->assertTrue($platform->games->contains($game));
        $this->get(route('games.show', $game))->assertOk()->assertSee('Ação')->assertSee('RPG')->assertSee('PC')->assertSee('Xbox');
        $this->get(route('games.index'))->assertOk()->assertSee('<li>Ação</li>', false)->assertSee('<li>RPG</li>', false);
    }

    public function test_validation_rejects_invalid_missing_and_repeated_ids_and_preserves_selections(): void
    {
        [$genre, $platform] = $this->taxonomy();
        $this->actingAs(User::factory()->administrator()->create());
        foreach ([['bad'], [99999], [$genre->id, $genre->id], [null]] as $ids) {
            $this->post(route('admin.games.store'), $this->payload(['genres' => $ids, 'platforms' => $ids]))
                ->assertSessionHasErrors(['genres.0', 'platforms.0']);
        }
        $this->post(route('admin.games.store'), $this->payload(['genres' => 'bad', 'platforms' => 'bad']))
            ->assertSessionHasErrors(['genres', 'platforms']);
        $this->from(route('admin.games.create'))->post(route('admin.games.store'), $this->payload([
            'title' => '', 'genres' => [$genre->id], 'platforms' => [$platform->id],
        ]))->assertSessionHasErrors('title')->assertSessionHasInput('genres', [$genre->id])->assertSessionHasInput('platforms', [$platform->id]);
        $html = $this->get(route('admin.games.create'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="genres\[\]"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/name="platforms\[\]"[^>]*checked/', $html);
        $this->assertDatabaseCount('games', 0);
        $this->assertDatabaseCount('game_genre', 0);
        $this->assertDatabaseCount('game_platform', 0);
    }

    public function test_storage_failure_rolls_back_game_and_all_relationships(): void
    {
        [$genre, $platform] = $this->taxonomy();
        $this->actingAs(User::factory()->administrator()->create());
        DB::statement("CREATE TRIGGER fail_platform_link BEFORE INSERT ON game_platform BEGIN SELECT RAISE(FAIL, 'Falha simulada no vínculo'); END");
        $this->withoutExceptionHandling();

        try {
            $this->post(route('admin.games.store'), $this->payload([
                'genres' => [$genre->id], 'platforms' => [$platform->id],
            ]));
            $this->fail('O cadastro deveria falhar ao gravar a plataforma.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('Falha simulada no vínculo', $exception->getMessage());
        } finally {
            DB::statement('DROP TRIGGER fail_platform_link');
        }

        $this->assertDatabaseCount('games', 0);
        $this->assertDatabaseCount('game_genre', 0);
        $this->assertDatabaseCount('game_platform', 0);
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
        $this->assertDatabaseHas('platforms', ['id' => $platform->id]);
    }

    public function test_guests_and_players_cannot_create_relationships(): void
    {
        [$genre, $platform] = $this->taxonomy();
        $data = $this->payload(['genres' => [$genre->id], 'platforms' => [$platform->id]]);
        $this->post(route('admin.games.store'), $data)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post(route('admin.games.store'), $data)->assertForbidden();
        $this->assertDatabaseCount('games', 0);
        $this->assertDatabaseCount('game_genre', 0);
        $this->assertDatabaseCount('game_platform', 0);
    }

    public function test_filters_combine_with_title_and_survive_pagination(): void
    {
        [$genre, $platform] = $this->taxonomy();
        $games = Game::factory()->count(13)->sequence(fn ($sequence) => ['title' => 'Aventura '.sprintf('%02d', $sequence->index)])->create();
        foreach ($games as $game) {
            $game->genres()->attach($genre);
            $game->platforms()->attach($platform);
        }
        $onlyGenre = Game::factory()->create(['title' => 'Aventura sem plataforma']);
        $onlyGenre->genres()->attach($genre);
        $onlyPlatform = Game::factory()->create(['title' => 'Aventura sem gênero']);
        $onlyPlatform->platforms()->attach($platform);
        $otherTitle = Game::factory()->create(['title' => 'Outro título']);
        $otherTitle->genres()->attach($genre);
        $otherTitle->platforms()->attach($platform);
        $filters = ['q' => 'aventura', 'genre' => $genre->id, 'platform' => $platform->id];
        $response = $this->get(route('games.index', $filters))->assertOk()->assertDontSee('Aventura sem')->assertDontSee('Outro título');
        $paginator = $response->viewData('games');
        $this->assertSame(13, $paginator->total());
        foreach ($filters as $key => $value) {
            $this->assertStringContainsString($key.'='.$value, $paginator->url(2));
        }
        foreach ($paginator as $game) {
            $this->assertTrue($game->relationLoaded('genres'));
            $this->assertTrue($game->relationLoaded('platforms'));
        }
        $this->get(route('games.index', [...$filters, 'page' => 2]))->assertOk()->assertSee('Aventura 12')->assertDontSee('Aventura 00');
        $this->get(route('games.index', ['genre' => $genre->id]))->assertViewHas('games', fn ($games) => $games->total() === 15);
        $this->get(route('games.index', ['platform' => $platform->id]))->assertViewHas('games', fn ($games) => $games->total() === 15);
        $this->get(route('games.index'))->assertViewHas('games', fn ($games) => $games->total() === 16);
    }

    public function test_catalog_query_count_does_not_grow_with_more_games(): void
    {
        [$genre, $platform] = $this->taxonomy();
        $game = Game::factory()->create();
        $game->genres()->attach($genre);
        $game->platforms()->attach($platform);

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get(route('games.index'))->assertOk();
        $singleGameQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        foreach (Game::factory()->count(11)->create() as $game) {
            $game->genres()->attach($genre);
            $game->platforms()->attach($platform);
        }

        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->get(route('games.index'))->assertOk();
        $fullPageQueries = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        $this->assertCount(12, $response->viewData('games'));
        $this->assertGreaterThan(0, $singleGameQueries);
        $this->assertSame($singleGameQueries, $fullPageQueries, 'Mais jogos não devem causar consultas individuais para cada vínculo.');
    }

    public function test_empty_relationships_and_invalid_filters(): void
    {
        $game = Game::factory()->create();
        $this->get(route('games.show', $game))->assertOk()->assertSee('Nenhum gênero informado.')->assertSee('Nenhuma plataforma informada.');
        foreach ([99999, 'invalid', ['1']] as $value) {
            $this->get(route('games.index', ['genre' => $value, 'platform' => $value]))->assertSessionHasErrors(['genre', 'platform']);
        }
    }

    public function test_database_rejects_duplicate_names_and_invalid_foreign_keys(): void
    {
        [$genre, $platform] = $this->taxonomy();
        $game = Game::factory()->create();
        foreach (['genres' => ['name' => $genre->name], 'platforms' => ['name' => $platform->name],
            'game_genre' => ['game_id' => $game->id, 'genre_id' => 99999],
            'game_platform' => ['game_id' => 99999, 'platform_id' => $platform->id]] as $table => $row) {
            try {
                DB::table($table)->insert($row);
                $this->fail('Uma restrição do banco não foi aplicada.');
            } catch (QueryException) {
                $this->assertDatabaseCount($table, in_array($table, ['genres', 'platforms']) ? 1 : 0);
            }
        }
    }

    public function test_database_rejects_duplicate_pivot_links(): void
    {
        [$genre, $platform] = $this->taxonomy();
        $game = Game::factory()->create();
        foreach (['game_genre' => ['genre_id' => $genre->id], 'game_platform' => ['platform_id' => $platform->id]] as $table => $ids) {
            $row = ['game_id' => $game->id, ...$ids];
            DB::table($table)->insert($row);
            try {
                DB::table($table)->insert($row);
                $this->fail('Vínculo duplicado foi aceito.');
            } catch (QueryException) {
                $this->assertDatabaseCount($table, 1);
            }
        }
        $game->delete();
        $this->assertDatabaseCount('game_genre', 0);
        $this->assertDatabaseCount('game_platform', 0);
    }
}
