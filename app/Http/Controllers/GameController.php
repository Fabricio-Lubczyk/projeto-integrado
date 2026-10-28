<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function show(Game $game): View
    {
        return view('games.show', compact('game'));
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']], [
            'q.string' => 'Informe um título válido.',
            'q.max' => 'Use até 100 caracteres na busca.',
        ]);
        $search = trim($data['q'] ?? '');
        $games = Game::query()
            ->when($search !== '', fn ($query) => $query->whereLike('title', '%'.$search.'%'))
            ->orderBy('title')->orderBy('id')->paginate(12)->withQueryString();

        return view('games.index', compact('games', 'search'));
    }
}
