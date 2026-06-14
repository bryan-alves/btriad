<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BindPlatformTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        if (! $tenant instanceof Tenant) {
            $tenant = Tenant::query()->findOrFail($tenant);
        }

        if ($tenant->is_platform) {
            abort(404, 'Este tenant não pode ser gerenciado por aqui.');
        }

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
