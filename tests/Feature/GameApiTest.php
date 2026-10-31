<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_is_public_and_returns_json(): void
    {
        $game = Game::factory()->create();
        $this->getJson(route('api.games.index'))->assertOk()->assertJsonFragment(['title' => $game->title]);
        $this->getJson(route('api.games.show', $game))->assertOk()->assertJsonPath('data.id', $game->id);
        $this->getJson('/api/games/99999')->assertNotFound();
    }

    public function test_only_admin_can_create_update_and_delete(): void
    {
        $game = Game::factory()->create();
        $data = ['title' => 'Jogo novo', 'developer' => 'Estúdio', 'description' => 'Descrição', 'released_on' => null];
        $this->postJson(route('api.games.store'), $data)->assertUnauthorized();
        $this->actingAs(User::factory()->create())->postJson(route('api.games.store'), $data)->assertForbidden();
        $this->actingAs(User::factory()->administrator()->create());
        $id = $this->postJson(route('api.games.store'), $data)->assertCreated()->json('data.id');
        $this->putJson(route('api.games.update', $id), [...$data, 'title' => 'Jogo atualizado'])->assertOk()->assertJsonPath('data.title', 'Jogo atualizado');
        $this->deleteJson(route('api.games.destroy', $id))->assertNoContent();
        $this->assertDatabaseMissing('games', ['id' => $id]);
        $this->assertDatabaseHas('games', ['id' => $game->id]);
    }

    public function test_validation_rejects_duplicate_title_and_invalid_relations(): void
    {
        $game = Game::factory()->create();
        $this->actingAs(User::factory()->administrator()->create())->postJson(route('api.games.store'), [
            'title' => $game->title, 'developer' => '', 'description' => '', 'genres' => [99999],
        ])->assertUnprocessable()->assertJsonValidationErrors(['title', 'developer', 'description', 'genres.0']);
    }
}
