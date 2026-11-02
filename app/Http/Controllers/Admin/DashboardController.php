<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Review;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'games' => Game::latest()->take(8)->get(),
            'gameCount' => Game::count(),
            'reviewCount' => Review::count(),
        ]);
    }
}
