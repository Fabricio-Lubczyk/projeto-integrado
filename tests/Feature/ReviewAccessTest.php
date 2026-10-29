<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_and_game_page_invites_guest_to_register(): void
    {
        $game = Game::factory()->create();

        $this->post(route('reviews.store', $game), ['rating' => 5, 'comment' => 'ComentÃ¡rio'])
            ->assertRedirect(route('login'));
        $this->get(route('games.show', $game))->assertOk()
            ->assertSee('Entre na sua conta ou crie uma conta para avaliar este jogo.')
            ->assertSee(route('login'))->assertSee(route('register'));
    }

    public function test_same_user_cannot_review_a_game_twice(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'game_id' => $game->id, 'rating' => 4, 'comment' => 'Primeira']);

        $this->actingAs($user)->from(route('games.show', $game))->post(route('reviews.store', $game), [
            'rating' => 2,
            'comment' => 'Segunda',
        ])->assertRedirect(route('games.show', $game))->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 1);
        $this->get(route('games.show', $game))->assertSee("Voc\u{00ea} j\u{00e1} enviou uma avalia\u{00e7}\u{00e3}o para este jogo.");
    }
}
