<?php

namespace Tests\Feature;

use App\Services\SteamGameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SteamImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_saves_images_and_updates_without_duplicate_game(): void
    {
        Http::preventStrayRequests();
        Http::fake(['store.steampowered.com/*' => Http::response([
            '730' => ['success' => true, 'data' => [
                'type' => 'game', 'name' => 'Counter-Strike 2', 'developers' => ['Valve'],
                'short_description' => 'Jogo de <b>ação</b>.',
                'release_date' => ['date' => '21 ago. 2012'],
                'header_image' => 'https://shared.akamai.steamstatic.com/test.jpg',
                'genres' => [['description' => 'Ação']],
                'screenshots' => [['path_full' => 'https://shared.akamai.steamstatic.com/shot.jpg']],
            ]],
        ])]);

        $service = app(SteamGameService::class);
        $game = $service->import(730);
        $same = $service->import(730);

        $this->assertSame($game->id, $same->id);
        $this->assertDatabaseCount('games', 1);
        $this->assertSame('Jogo de ação.', $same->description);
        $this->assertCount(1, $same->screenshots);
        $this->assertSame('2012-08-21', $same->released_on->format('Y-m-d'));
        $this->assertSame('Ação', $same->genres->first()->name);
        $this->assertSame('PC', $same->platforms->first()->name);
        $this->get(route('games.show', $game))->assertOk()->assertSee($game->cover_url, false);
    }

    public function test_failed_lookup_does_not_create_a_game(): void
    {
        Http::fake(['store.steampowered.com/*' => Http::response(['999999' => ['success' => false]])]);
        try {
            app(SteamGameService::class)->import(999999);
            $this->fail('A importação inválida deveria falhar.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('steam_app_id', $exception->errors());
        }
        $this->assertDatabaseCount('games', 0);
    }
}
