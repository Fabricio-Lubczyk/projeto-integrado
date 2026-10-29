<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Game;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Game $game): RedirectResponse
    {
        $user = $request->user();

        if ($user->reviews()->where('game_id', $game->id)->exists()) {
            return to_route('games.show', $game)->withErrors([
                'review' => 'Você já enviou uma avaliação para este jogo.',
            ])->withInput();
        }

        try {
            $user->reviews()->create($request->validated() + ['game_id' => $game->id]);
        } catch (UniqueConstraintViolationException) {
            return to_route('games.show', $game)->withErrors([
                'review' => 'Você já enviou uma avaliação para este jogo.',
            ])->withInput();
        }

        return to_route('games.show', $game)->with('status', 'Sua avaliação foi enviada.');
    }
}
