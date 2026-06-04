<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $domain = Domain::with('tenant')
            ->where('domain', $request->getHost())
            ->first();

        if ($domain === null) {
            abort(404, 'Tenant não encontrado para este domínio.');
        }

        app()->instance('tenant', $domain->tenant);

        return $next($request);
    }
}
