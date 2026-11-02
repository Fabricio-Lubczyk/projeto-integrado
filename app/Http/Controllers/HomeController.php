<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Genre::has('games')
            ->whereNotIn('name', ['Grátis para Jogar', 'Free To Play', 'Gratuito para jogar'])
            ->withCount('games')->orderByDesc('games_count')->orderBy('name')->take(6)
            ->with(['games' => fn ($query) => $query->with(['genres', 'platforms'])
                ->withAvg('reviews', 'rating')->withCount('reviews')
                ->orderBy('games.title')->orderBy('games.id')->limit(4)])
            ->get();

        return view('welcome', [
            'categories' => $categories,
            'games' => Game::with(['genres', 'platforms'])->withAvg('reviews', 'rating')->withCount('reviews')->latest()->orderByDesc('id')->take(12)->get(),
            'topGames' => Game::withAvg('reviews', 'rating')->withCount('reviews')->has('reviews')->orderByDesc('reviews_avg_rating')->orderBy('id')->take(5)->get(),
            'latestReviews' => Review::with(['user', 'game'])->latest()->take(3)->get(),
        ]);
    }
}
