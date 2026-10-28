<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_has_an_empty_state(): void
    {
        $this->get(route('games.index'))->assertOk()->assertSee('Nenhum jogo cadastrado');
    }

    public function test_search_is_case_insensitive_and_pagination_preserves_query(): void
    {
        Game::factory()->count(13)->sequence(fn ($sequence) => ['title' => 'Aventura '.str_pad($sequence->index, 2, '0', STR_PAD_LEFT)])->create();
        Game::factory()->create(['title' => 'Outro título']);
        $response = $this->get(route('games.index', ['q' => 'aventura']));
        $response->assertOk()->assertSee('Aventura 00')->assertDontSee('Outro título')->assertDontSee('Aventura 12');
        $response->assertViewHas('games', fn ($games) => $games->total() === 13 && $games->count() === 12);
        $this->assertStringContainsString('q=aventura', $response->viewData('games')->url(2));
        $this->get(route('games.index', ['q' => 'aventura', 'page' => 2]))->assertOk()->assertSee('Aventura 12')->assertDontSee('Aventura 00');
    }

    public function test_search_handles_no_results_and_rejects_invalid_input(): void
    {
        $this->get(route('games.index', ['q' => 'inexistente']))->assertOk()->assertSee('Nenhum jogo encontrado');
        $this->get(route('games.index', ['q' => ['teste']]))->assertSessionHasErrors('q');
        $this->get(route('games.index', ['q' => str_repeat('a', 101)]))->assertSessionHasErrors('q');
    }
}
