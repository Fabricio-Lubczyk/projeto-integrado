<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_page_lists_reviews_safely_in_reverse_chronological_order(): void
    {
        $game = Game::factory()->create();
        $older = User::factory()->create(['name' => 'Jogador Antigo']);
        $newer = User::factory()->create(['name' => 'Jogador Recente']);
        $olderReview = Review::factory()->create(['user_id' => $older->id, 'game_id' => $game->id, 'rating' => 3, 'comment' => '<script>alert(1)</script>']);
        $olderReview->forceFill(['created_at' => now()->subDay()])->save();
        Review::factory()->create(['user_id' => $newer->id, 'game_id' => $game->id, 'rating' => 5, 'comment' => 'Muito bom']);

        $response = $this->get(route('games.show', $game))->assertOk()
            ->assertSee('Jogador Antigo')->assertSee('Jogador Recente')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Muito bom')->assertSee('5 de 5');

        $recentPosition = strpos($response->getContent(), 'Jogador Recente');
        $olderPosition = strpos($response->getContent(), 'Jogador Antigo');
        $this->assertTrue($recentPosition < $olderPosition);
    }

    public function test_game_page_shows_empty_state_when_there_are_no_reviews(): void
    {
        $game = Game::factory()->create();

        $this->get(route('games.show', $game))->assertOk()
            ->assertSee("Ainda n\u{00e3}o h\u{00e1} avalia\u{00e7}\u{00f5}es. Seja a primeira pessoa a avaliar este jogo.");
    }
}
