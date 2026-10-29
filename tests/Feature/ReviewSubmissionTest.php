<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_review_a_game_and_is_the_author(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $game = Game::factory()->create();

        $this->actingAs($user)->post(route('reviews.store', $game), [
            'rating' => '5',
            'comment' => 'Uma otima experiencia.',
            'user_id' => $otherUser->id,
        ])->assertRedirect(route('games.show', $game))->assertSessionHas('status');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'rating' => 5,
            'comment' => 'Uma otima experiencia.',
        ]);
        $this->assertSame($user->id, $user->reviews()->first()->user_id);
    }

    public function test_review_rating_and_comment_are_validated_in_portuguese(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        $this->actingAs($user)->from(route('games.show', $game))->post(route('reviews.store', $game), [
            'rating' => 6,
            'comment' => '',
        ])->assertRedirect(route('games.show', $game))
            ->assertSessionHasErrors(['rating', 'comment'])
            ->assertSessionHasInput('rating', 6);

        $this->actingAs($user)->from(route('games.show', $game))->post(route('reviews.store', $game), [
            'rating' => 1,
            'comment' => str_repeat('a', 2001),
        ])->assertSessionHasErrors('comment')->assertSessionHasInput('rating', 1);

        $this->actingAs($user)->post(route('reviews.store', $game), [
            'rating' => 0,
            'comment' => 'Comentario valido',
        ])->assertSessionHasErrors('rating');
    }
}
