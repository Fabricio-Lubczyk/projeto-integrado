<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class SteamGameService
{
    public function import(int $appId): Game
    {
        $response = Http::timeout(25)->retry(2, 1000)->get('https://store.steampowered.com/api/appdetails', [
            'appids' => $appId, 'cc' => 'br', 'l' => 'portuguese',
        ])->throw()->json((string) $appId);

        if (! ($response['success'] ?? false) || ($response['data']['type'] ?? '') !== 'game') {
            throw ValidationException::withMessages(['steam_app_id' => 'O identificador não corresponde a um jogo disponível na Steam.']);
        }

        $data = $response['data'];
        $softwareGenres = ['Utilidades', 'Animação e Modelação', 'Design e Ilustração',
            'Produção de Vídeo', 'Software para Treino', 'Edição de Fotografias'];
        if (collect($data['genres'] ?? [])->pluck('description')->intersect($softwareGenres)->isNotEmpty()) {
            throw ValidationException::withMessages(['steam_app_id' => 'Este item é um aplicativo, não um jogo do catálogo.']);
        }

        return DB::transaction(function () use ($appId, $data) {
            $game = Game::where('steam_app_id', $appId)->first()
                ?? Game::where('title', $data['name'])->first()
                ?? new Game;
            $game->fill([
                'title' => $data['name'], 'developer' => mb_substr(implode(', ', $data['developers'] ?? []), 0, 120),
                'description' => html_entity_decode(strip_tags($data['short_description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'released_on' => $this->releaseDate($data['release_date']['date'] ?? ''),
                'steam_app_id' => $appId,
                'cover_url' => $data['header_image'] ?? null,
                'screenshots' => array_slice(array_column($data['screenshots'] ?? [], 'path_full'), 0, 3),
            ]);
            $game->save();
            $genres = collect($data['genres'] ?? [])->pluck('description')->map(
                fn ($name) => Genre::firstOrCreate(['name' => $name])->id
            );
            $game->genres()->sync($genres);
            $game->platforms()->sync([Platform::firstOrCreate(['name' => 'PC'])->id]);

            return $game;
        });
    }

    private function releaseDate(string $value): ?string
    {
        $months = ['jan' => 1, 'fev' => 2, 'feb' => 2, 'mar' => 3, 'abr' => 4, 'apr' => 4,
            'mai' => 5, 'may' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8, 'aug' => 8,
            'set' => 9, 'sep' => 9, 'out' => 10, 'oct' => 10, 'nov' => 11, 'dez' => 12, 'dec' => 12];
        if (! preg_match('/(\d{1,2})[\/\s]+([\p{L}]+)\.?[,\/\s]+(\d{4})/u', $value, $matches)) {
            return null;
        }
        $month = $months[mb_strtolower(mb_substr($matches[2], 0, 3))] ?? null;
        if (! $month || ! checkdate($month, (int) $matches[1], (int) $matches[3])) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $matches[3], $month, $matches[1]);
    }
}
