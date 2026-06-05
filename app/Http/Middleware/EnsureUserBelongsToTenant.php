<?php

namespace App\Http\Middleware;

use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = CurrentTenant::id();
        $user = $request->user();

        if ($tenantId === null || $user === null || (int) $user->tenant_id !== (int) $tenantId) {
            abort(403, 'Usuário não pertence a este tenant.');
        }

        return $next($request);
    }
}
