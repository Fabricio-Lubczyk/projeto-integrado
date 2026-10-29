<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_reviews_are_paginated_ten_per_page(): void
    {
        $game = Game::factory()->create();

        foreach (range(1, 11) as $number) {
            $review = Review::factory()->create([
                'user_id' => User::factory()->create()->id,
                'game_id' => $game->id,
                'comment' => 'ComentÃ¡rio '.$number,
            ]);
            $review->forceFill(['created_at' => now()->subMinutes(12 - $number)])->save();
        }

        $firstPage = $this->get(route('games.show', $game))->assertOk()
            ->assertSee('ComentÃ¡rio 11')->assertDontSee('ComentÃ¡rio 1</p>');
        $this->assertSame(10, $firstPage->viewData('reviews')->count());

        $this->get(route('games.show', ['game' => $game, 'page' => 2]))->assertOk()
            ->assertSee('ComentÃ¡rio 1');
    }
}
