<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next, string $scope = 'catalog'): Response
    {
        $user = $request->user();
        abort_unless($scope === 'users' ? $user?->is_admin === true : $user?->canManageGames(), 403);

        return $next($request);
    }
}
