<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGameListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_list_games(): void
    {
        $game = Game::factory()->create();
        $this->get(route('admin.games.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.games.index'))->assertForbidden();
        $this->actingAs(User::factory()->administrator()->create())->get(route('admin.games.index'))
            ->assertOk()->assertSee($game->title)->assertSee($game->developer)
            ->assertSee(route('games.show', $game));
    }

    public function test_list_is_sorted_by_title_and_paginated(): void
    {
        Game::factory()->create(['title' => 'Zelda']);
        Game::factory()->count(12)->sequence(fn ($sequence) => ['title' => 'Aventura '.sprintf('%02d', $sequence->index)])->create();
        $this->actingAs(User::factory()->administrator()->create());

        $response = $this->get(route('admin.games.index'))->assertOk()
            ->assertSeeInOrder(['Aventura 00', 'Aventura 01', 'Aventura 11'])->assertDontSee('Zelda')
            ->assertSee('Página 1 de 2')->assertSee('Próxima');
        $this->assertSame(13, $response->viewData('games')->total());
        $this->assertCount(12, $response->viewData('games'));
        $this->get(route('admin.games.index', ['page' => 2]))->assertOk()->assertSee('Zelda')->assertDontSee('Aventura 00')
            ->assertSee('Página 2 de 2')->assertSee('Anterior');
    }

    public function test_title_search_survives_pagination_and_can_be_cleared(): void
    {
        Game::factory()->count(13)->sequence(fn ($sequence) => ['title' => 'Aventura '.sprintf('%02d', $sequence->index)])->create();
        Game::factory()->create(['title' => 'Outro título', 'developer' => 'Aventura Studios']);
        $this->actingAs(User::factory()->administrator()->create());

        $response = $this->get(route('admin.games.index', ['q' => '  aventura  ']))
            ->assertOk()->assertSee('Aventura 00')->assertDontSee('Outro título')->assertSee('Limpar busca');
        $games = $response->viewData('games');
        $this->assertSame(13, $games->total());
        $response->assertSee($games->nextPageUrl());
        parse_str(parse_url($games->url(2), PHP_URL_QUERY), $query);
        $this->assertSame('aventura', trim($query['q']));
        $this->get($games->url(2))->assertOk()->assertSee('Aventura 12')->assertDontSee('Aventura 00');
        $this->get(route('admin.games.index'))->assertViewHas('games', fn ($games) => $games->total() === 14);
    }

    public function test_empty_list_and_search_without_results_have_helpful_links(): void
    {
        $this->actingAs(User::factory()->administrator()->create());
        $this->get(route('admin.games.index'))->assertOk()
            ->assertSee('Nenhum jogo cadastrado')->assertSee('Cadastrar o primeiro jogo')
            ->assertSee(route('admin.games.create'));
        Game::factory()->create(['title' => 'Hollow Knight']);
        $this->get(route('admin.games.index', ['q' => 'inexistente']))->assertOk()
            ->assertSee('Nenhum jogo encontrado para esta busca.')->assertSee('Limpar busca');
    }

    public function test_invalid_search_is_rejected(): void
    {
        $this->actingAs(User::factory()->administrator()->create());
        foreach ([['teste'], str_repeat('a', 101)] as $search) {
            $this->get(route('admin.games.index', ['q' => $search]))->assertSessionHasErrors('q');
        }
    }

    public function test_dashboard_links_to_management_and_registration(): void
    {
        $this->actingAs(User::factory()->administrator()->create())->get(route('admin.index'))
            ->assertOk()->assertSee(route('admin.games.index'))->assertSee(route('admin.games.create'));
    }
}
