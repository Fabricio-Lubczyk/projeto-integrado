<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_details_are_public_and_escape_html(): void
    {
        $game = Game::factory()->create(['title' => 'Jogo de teste', 'released_on' => null, 'description' => '<script>alert(1)</script>']);
        $this->get(route('games.show', $game))->assertOk()->assertSee('Jogo de teste')->assertSee($game->developer)
            ->assertSee('Não informado')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('games.index'))->assertSee(route('games.show', $game));
    }

    public function test_unknown_or_invalid_game_returns_not_found(): void
    {
        $this->get('/jogos/99999')->assertNotFound();
        $this->get('/jogos/invalido')->assertNotFound();
    }
}
