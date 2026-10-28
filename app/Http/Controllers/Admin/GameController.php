<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameRequest;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GameController extends Controller
{
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
}
