<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return GameResource::collection(Game::with(['genres', 'platforms'])->orderBy('title')->orderBy('id')->paginate(12));
    }

    public function show(Game $game): GameResource
    {
        return new GameResource($game->load(['genres', 'platforms']));
    }

    public function store(StoreGameRequest $request): GameResource
    {
        $game = DB::transaction(function () use ($request) {
            $game = Game::create($request->safe()->except(['genres', 'platforms']));
            $game->genres()->sync($request->validated('genres', []));
            $game->platforms()->sync($request->validated('platforms', []));

            return $game;
        });

        return new GameResource($game->load(['genres', 'platforms']));
    }

    public function update(UpdateGameRequest $request, Game $game): GameResource
    {
        DB::transaction(function () use ($request, $game) {
            $game->update($request->safe()->except(['genres', 'platforms']));
            if ($request->has('genres')) {
                $game->genres()->sync($request->validated('genres', []));
            }
            if ($request->has('platforms')) {
                $game->platforms()->sync($request->validated('platforms', []));
            }
        });

        return new GameResource($game->load(['genres', 'platforms']));
    }

    public function destroy(Game $game): Response
    {
        $game->delete();

        return response()->noContent();
    }
}
