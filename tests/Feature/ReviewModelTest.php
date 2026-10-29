<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_relationships_and_database_unique_constraint(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'game_id' => $game->id, 'rating' => 4, 'comment' => 'Boa']);

        $this->assertTrue($review->user->is($user));
        $this->assertTrue($review->game->is($game));
        $this->assertTrue($user->reviews->first()->is($review));
        $this->assertTrue($game->reviews->first()->is($review));

        try {
            Review::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);
            $this->fail('A restriÃ§Ã£o Ãºnica deveria impedir uma segunda avaliaÃ§Ã£o.');
        } catch (QueryException) {
            $this->assertDatabaseCount('reviews', 1);
        }
    }
}
