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

    public function test_dashboard_links_to_management_and_registration(): void
    {
        $this->actingAs(User::factory()->administrator()->create())->get(route('admin.index'))
            ->assertOk()->assertSee(route('admin.games.index'))->assertSee(route('admin.games.create'));
    }
}
