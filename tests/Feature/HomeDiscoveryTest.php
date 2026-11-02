<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_groups_games_by_genre_and_limits_each_section(): void
    {
        $action = Genre::create(['name' => 'Ação']);
        $rpg = Genre::create(['name' => 'RPG']);
        $empty = Genre::create(['name' => 'Sem jogos']);
        $games = Game::factory()->count(7)->create();
        $action->games()->attach($games->modelKeys());
        $rpg->games()->attach($games->first()->id);

        $response = $this->get(route('inicio'))->assertOk();
        $response->assertSee('categoria-'.$action->id)->assertSee('categoria-'.$rpg->id);
        $response->assertDontSee('categoria-'.$empty->id);
        $response->assertSee(route('games.index', ['genre' => $action->id]));
        $response->assertViewHas('categories', fn ($categories) => $categories->firstWhere('id', $action->id)->games->count() === 4
            && $categories->firstWhere('id', $rpg->id)->games->count() === 1);
        $response->assertSee('data-carousel', false)->assertSee('Próximo destaque');
    }

    public function test_empty_catalog_does_not_render_empty_carousel(): void
    {
        $this->get(route('inicio'))->assertOk()->assertDontSee('data-carousel', false)
            ->assertSee('Nenhum jogo cadastrado');
    }
}
