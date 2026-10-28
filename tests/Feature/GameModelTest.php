<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_keeps_its_details_and_casts_release_date(): void
    {
        $game = Game::factory()->create(['released_on' => '2020-03-20']);
        $this->assertDatabaseHas('games', ['id' => $game->id, 'title' => $game->title]);
        $this->assertSame('20/03/2020', $game->fresh()->released_on->format('d/m/Y'));
        $this->assertNull(Game::factory()->create(['released_on' => null])->released_on);
    }
}
