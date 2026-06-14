<?php

namespace App\Http\Middleware;

use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcademySiteManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role !== 'admin') {
            abort(403, 'Você não tem permissão para gerenciar este site.');
        }

        $tenant = CurrentTenant::get();

        if ($tenant?->is_platform) {
            abort(403, 'Use o painel Tatameiro para gerenciar academias.');
        }

        return $next($request);
    }
}
