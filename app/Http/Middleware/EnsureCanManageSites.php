<?php

namespace App\Http\Middleware;

use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageSites
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user?->username !== 'bryanalves'
            //|| CurrentTenant::get()?->slug !== 'btriad'
            //|| $request->getHost() !== 'btriadjiujitsu.com.br'
        ) {
            abort(403, 'Você não tem permissão para gerenciar sites.');
        }

        return $next($request);
    }
}
