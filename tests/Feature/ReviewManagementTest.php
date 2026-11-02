<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_edit_and_delete_review(): void
    {
        $review = Review::factory()->create();
        $this->actingAs($review->user)->get(route('reviews.edit', $review))->assertOk();
        $this->put(route('reviews.update', $review), ['rating' => 4, 'comment' => 'Muito bom'])
            ->assertRedirect(route('games.show', $review->game_id));
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 4, 'comment' => 'Muito bom']);
        $this->delete(route('reviews.destroy', $review))->assertRedirect(route('games.show', $review->game_id));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_other_users_and_guests_cannot_modify_review(): void
    {
        $review = Review::factory()->create();
        $this->get(route('reviews.edit', $review))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->get(route('reviews.edit', $review))->assertForbidden();
        $this->put(route('reviews.update', $review), [])->assertForbidden();
        $this->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_rating_summary_and_personal_history(): void
    {
        $game = Game::factory()->create();
        $first = Review::factory()->create(['game_id' => $game->id, 'rating' => 3]);
        $second = Review::factory()->create(['game_id' => $game->id, 'rating' => 4]);
        $this->get(route('games.show', $game))->assertSee('3,5 de 5')->assertSee('2 avaliações');
        $this->actingAs($first->user)->get(route('account.show'))->assertSee($game->title)->assertSee('3 de 5')->assertDontSee('4 de 5');
        $this->delete(route('reviews.destroy', $first));
        $this->get(route('games.show', $game))->assertSee('4,0 de 5')->assertSee('1 avaliação');
        $second->delete();
        $this->get(route('games.show', $game))->assertSee('Ainda sem avaliações.');
    }
}
