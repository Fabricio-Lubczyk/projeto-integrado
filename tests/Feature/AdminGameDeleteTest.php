<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGameDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_regular_users_cannot_confirm_or_delete_games(): void
    {
        [$game, $genre, $platform, $review] = $this->gameWithRelationships();

        $this->get(route('admin.games.delete', $game))->assertRedirect(route('login'));
        $this->delete(route('admin.games.destroy', $game), ['confirm' => '1'])->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.games.delete', $game))->assertForbidden();
        $this->delete(route('admin.games.destroy', $game), ['confirm' => '1'])->assertForbidden();

        $this->assertGameAndRelationshipsExist($game, $genre, $platform, $review);
    }

    public function test_admin_can_view_confirmation_with_explicit_delete_form_without_deleting_anything(): void
    {
        [$game, $genre, $platform, $review] = $this->gameWithRelationships();

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.games.delete', $game))->assertOk()
            ->assertSee($game->title)
            ->assertSee('Este jogo e todas as suas avaliações serão excluídos. Esta ação não pode ser desfeita.')
            ->assertSee('action="'.route('admin.games.destroy', $game).'"', false)
            ->assertSee('name="_method" value="DELETE"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="confirm"', false)
            ->assertSee('href="'.route('admin.games.index').'"', false);

        $this->assertGameAndRelationshipsExist($game, $genre, $platform, $review);
    }

    public function test_get_requests_never_delete_even_with_confirmation_in_the_query_string(): void
    {
        [$game, $genre, $platform, $review] = $this->gameWithRelationships();
        $this->actingAs(User::factory()->administrator()->create());

        $this->get(route('admin.games.delete', ['game' => $game, 'confirm' => '1']))->assertOk();
        $this->get(route('admin.games.destroy', ['game' => $game, 'confirm' => '1']))->assertStatus(405);

        $this->assertGameAndRelationshipsExist($game, $genre, $platform, $review);
    }

    public function test_missing_or_rejected_confirmation_preserves_the_game_and_its_data(): void
    {
        [$game, $genre, $platform, $review] = $this->gameWithRelationships();
        $this->actingAs(User::factory()->administrator()->create());

        foreach ([[], ['confirm' => '0'], ['confirm' => false], ['confirm' => 'nao']] as $data) {
            $this->from(route('admin.games.delete', $game))
                ->delete(route('admin.games.destroy', $game), $data)
                ->assertRedirect(route('admin.games.delete', $game))->assertSessionHasErrors('confirm');

            $this->assertGameAndRelationshipsExist($game, $genre, $platform, $review);
        }

        $this->get(route('admin.games.delete', $game))->assertOk()->assertSee($game->title);
    }

    public function test_confirmed_delete_cascades_reviews_and_links_but_preserves_shared_taxonomies(): void
    {
        [$game, $genre, $platform, $review] = $this->gameWithRelationships();
        $otherGame = Game::factory()->create(['title' => 'Outro jogo']);
        $otherGame->genres()->attach($genre);
        $otherGame->platforms()->attach($platform);
        $otherReview = Review::factory()->create(['game_id' => $otherGame->id, 'user_id' => $review->user_id]);
        $secondReview = Review::factory()->create(['game_id' => $game->id]);

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('admin.games.destroy', $game), ['confirm' => '1'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.games.index'))->assertSessionHas('status');

        $this->assertDatabaseMissing('games', ['id' => $game->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $secondReview->id]);
        $this->assertDatabaseMissing('game_genre', ['game_id' => $game->id]);
        $this->assertDatabaseMissing('game_platform', ['game_id' => $game->id]);
        $this->assertGameAndRelationshipsExist($otherGame, $genre, $platform, $otherReview);
        $this->assertDatabaseHas('users', ['id' => $review->user_id]);
        $this->assertDatabaseHas('users', ['id' => $secondReview->user_id]);
        $this->assertDatabaseCount('games', 1);
        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseCount('game_genre', 1);
        $this->assertDatabaseCount('game_platform', 1);
        $this->assertDatabaseCount('genres', 1);
        $this->assertDatabaseCount('platforms', 1);
        $this->get(route('games.show', $game))->assertNotFound();
    }

    public function test_admin_can_delete_a_game_without_reviews_or_relationships(): void
    {
        $game = Game::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('admin.games.destroy', $game), ['confirm' => 'on'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.games.index'));

        $this->assertDatabaseCount('games', 0);
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_missing_game_returns_not_found_for_confirmation_and_delete(): void
    {
        $this->actingAs(User::factory()->administrator()->create());

        $this->get(route('admin.games.delete', 99999))->assertNotFound();
        $this->delete(route('admin.games.destroy', 99999), ['confirm' => '1'])->assertNotFound();

        $this->assertDatabaseCount('games', 0);
    }

    public function test_delete_requires_a_valid_csrf_token_with_normal_middleware(): void
    {
        [$game, $genre, $platform, $review] = $this->gameWithRelationships();
        $this->actingAs(User::factory()->administrator()->create());
        $environment = $this->app->environment();
        $this->app->instance('env', 'local');

        try {
            $this->withSession(['_token' => 'token-da-sessao'])
                ->delete(route('admin.games.destroy', $game), ['confirm' => '1'])->assertStatus(419);
            $this->assertGameAndRelationshipsExist($game, $genre, $platform, $review);

            $this->withSession(['_token' => 'token-da-sessao'])
                ->delete(route('admin.games.destroy', $game), ['confirm' => '1', '_token' => 'token-da-sessao'])
                ->assertSessionHasNoErrors()->assertRedirect(route('admin.games.index'));
        } finally {
            $this->app->instance('env', $environment);
        }

        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    private function gameWithRelationships(): array
    {
        $game = Game::factory()->create(['title' => 'Jogo para excluir']);
        $genre = Genre::create(['name' => 'Aventura']);
        $platform = Platform::create(['name' => 'PC']);
        $game->genres()->attach($genre);
        $game->platforms()->attach($platform);
        $review = Review::factory()->create(['game_id' => $game->id]);

        return [$game, $genre, $platform, $review];
    }

    private function assertGameAndRelationshipsExist(Game $game, Genre $genre, Platform $platform, Review $review): void
    {
        $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => $game->title]);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'game_id' => $game->id]);
        $this->assertDatabaseHas('game_genre', ['game_id' => $game->id, 'genre_id' => $genre->id]);
        $this->assertDatabaseHas('game_platform', ['game_id' => $game->id, 'platform_id' => $platform->id]);
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
        $this->assertDatabaseHas('platforms', ['id' => $platform->id]);
    }
}
