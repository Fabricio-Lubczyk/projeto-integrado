<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewCascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_user_or_game_cascades_to_its_reviews(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $firstGame = Game::factory()->create();
        $secondGame = Game::factory()->create();

        Review::factory()->create(['user_id' => $firstUser->id, 'game_id' => $firstGame->id]);
        Review::factory()->create(['user_id' => $secondUser->id, 'game_id' => $firstGame->id]);
        Review::factory()->create(['user_id' => $firstUser->id, 'game_id' => $secondGame->id]);

        $firstUser->delete();
        $this->assertDatabaseCount('reviews', 1);

        $firstGame->delete();
        $this->assertDatabaseCount('reviews', 0);
    }
}
