<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GameController extends Controller
{
    public function create(): View
    {
        return view('admin.games.create');
    }

    public function store(StoreGameRequest $request): RedirectResponse
    {
        Game::create($request->validated());

        return to_route('admin.games.create')->with('status', 'Jogo cadastrado no catálogo.');
    }
}
