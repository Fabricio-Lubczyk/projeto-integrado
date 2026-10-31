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
