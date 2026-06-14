<?php

namespace App\Http\Controllers;

use App\Support\CurrentTenant;
use App\Support\PwaBranding;
use App\Support\TenantPwaIcons;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminPwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $tenant = CurrentTenant::get();
        $site = $tenant?->site;
        $academyName = $site?->academy_name ?? $tenant?->name ?? 'Academia';
        $shortName = PwaBranding::shortName($tenant, $site);
        $themeColor = $site?->app_header_color ?? '#1b1b18';
        $backgroundColor = $site?->app_background_color ?? '#f3f4f6';

        return response()->json([
            'name' => $shortName,
            'short_name' => $shortName,
            'description' => "Painel de gestão da {$academyName}",
            'start_url' => '/admin/students',
            'scope' => '/',
            'id' => '/admin/',
            'display' => 'standalone',
            'orientation' => 'any',
            'lang' => 'pt-BR',
            'dir' => 'ltr',
            'theme_color' => $themeColor,
            'background_color' => $backgroundColor,
            'icons' => TenantPwaIcons::manifestIcons(),
        ], 200, [
            'Content-Type' => 'application/manifest+json; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function icon(int $size): Response
    {
        return TenantPwaIcons::response($size);
    }
}
