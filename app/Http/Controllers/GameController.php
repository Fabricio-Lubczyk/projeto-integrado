<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function show(Game $game): View
    {
        $game->load(['genres', 'platforms']);

        return view('games.show', compact('game'));
    }

    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'genre' => ['nullable', 'integer', 'exists:genres,id'],
            'platform' => ['nullable', 'integer', 'exists:platforms,id'],
        ], [
            'integer' => 'Selecione um filtro válido.',
            'exists' => 'A opção selecionada não está disponível.',
            'q.string' => 'Informe um título válido.',
            'q.max' => 'Use até 100 caracteres na busca.',
        ]);
        $search = trim($data['q'] ?? '');
        $genre = $data['genre'] ?? '';
        $platform = $data['platform'] ?? '';
        $genres = Genre::orderBy('name')->get();
        $platforms = Platform::orderBy('name')->get();
        $games = Game::query()->with(['genres', 'platforms'])
            ->when($genre !== '', fn ($query) => $query->whereHas('genres', fn ($query) => $query->whereKey($genre)))
            ->when($platform !== '', fn ($query) => $query->whereHas('platforms', fn ($query) => $query->whereKey($platform)))
            ->when($search !== '', fn ($query) => $query->whereLike('title', '%'.$search.'%'))
            ->orderBy('title')->orderBy('id')->paginate(12)->withQueryString();

        return view('games.index', compact('games', 'search', 'genres', 'platforms', 'genre', 'platform'));
    }
}
