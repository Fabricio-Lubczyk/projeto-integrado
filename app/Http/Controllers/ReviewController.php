<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Game;
use App\Models\Review;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function edit(Request $request, Review $review): View
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        return view('games.reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id, 403);
        $review->update($request->validated());

        return to_route('games.show', $review->game_id)->with('status', 'Avaliação atualizada.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id, 403);
        $gameId = $review->game_id;
        $review->delete();

        return to_route('games.show', $gameId)->with('status', 'Avaliação excluída.');
    }

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
