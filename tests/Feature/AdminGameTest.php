<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGameTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return ['title' => 'Hollow Knight', 'developer' => 'Team Cherry', 'released_on' => '2017-02-24', 'description' => 'Uma aventura em um reino subterrâneo.'];
    }

    public function test_only_administrators_can_create_games(): void
    {
        $this->get(route('admin.games.create'))->assertRedirect(route('login'));
        $this->post(route('admin.games.store'), $this->data())->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.games.create'))->assertForbidden();
        $this->post(route('admin.games.store'), $this->data())->assertForbidden();
        $this->assertDatabaseCount('games', 0);
    }

    public function test_admin_can_register_game_with_optional_release_date(): void
    {
        $this->actingAs(User::factory()->administrator()->create())->get(route('admin.games.create'))->assertOk();
        $data = $this->data();
        $data['title'] = '  Hollow Knight  ';
        $data['released_on'] = null;
        $this->post(route('admin.games.store'), $data)->assertRedirect(route('admin.games.create'))->assertSessionHas('status');
        $this->assertDatabaseHas('games', ['title' => 'Hollow Knight', 'released_on' => null]);
    }

    public function test_invalid_or_duplicate_games_are_not_saved(): void
    {
        Game::factory()->create(['title' => 'Hollow Knight']);
        $this->actingAs(User::factory()->administrator()->create());
        $this->post(route('admin.games.store'), [...$this->data(), 'developer' => '', 'released_on' => '2026-02-30', 'description' => ''])
            ->assertSessionHasErrors(['title', 'developer', 'released_on', 'description']);
        $this->post(route('admin.games.store'), [...$this->data(), 'title' => str_repeat('a', 151), 'description' => str_repeat('a', 5001)])
            ->assertSessionHasErrors(['title', 'description']);
        $this->assertDatabaseCount('games', 1);
    }
}
