<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ], [
            'q.string' => 'Informe um título válido.',
            'q.max' => 'Use até 100 caracteres na busca.',
        ]);
        $search = trim($data['q'] ?? '');
        $games = Game::query()
            ->when($search !== '', fn ($query) => $query->whereLike('title', '%'.$search.'%'))
            ->orderBy('title')->orderBy('id')->paginate(12)->withQueryString();

        return view('admin.games.index', compact('games', 'search'));
    }

    public function create(): View
    {
        return view('admin.games.create', [
            'genres' => Genre::orderBy('name')->get(),
            'platforms' => Platform::orderBy('name')->get(),
        ]);
    }

    public function store(StoreGameRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $game = Game::create($request->safe()->except(['genres', 'platforms']));
            $game->genres()->sync($request->validated('genres', []));
            $game->platforms()->sync($request->validated('platforms', []));
        });

        return to_route('admin.games.create')->with('status', 'Jogo cadastrado no catálogo.');
    }

    public function update(UpdateGameRequest $request, Game $game): RedirectResponse
    {
        DB::transaction(function () use ($request, $game) {
            $game->update($request->safe()->except(['genres', 'platforms']));
            $game->genres()->sync($request->validated('genres', []));
            $game->platforms()->sync($request->validated('platforms', []));
        });

        return to_route('admin.games.index')->with('status', 'Jogo atualizado no catálogo.');
    }
}
