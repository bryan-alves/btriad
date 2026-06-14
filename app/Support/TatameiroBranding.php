<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\TenantSite;

class TatameiroBranding
{
    public const APP_LOGO = 'tatameiro-app-logo.png';

    public const FAVICON = 'tatameiro-favicon.png';

    public const MARKETING_LOGO = 'tatameiro-logo.png';

    public static function is(?Tenant $tenant): bool
    {
        return $tenant?->slug === 'tatameiro' || (bool) $tenant?->is_platform;
    }

    public static function appLogoUrl(?TenantSite $site = null, ?Tenant $tenant = null): string
    {
        if (static::is($tenant)) {
            return asset(static::APP_LOGO);
        }

        return $site?->logo_url ?? asset('img/logo/triangulo.png');
    }

    public static function faviconUrl(?TenantSite $site = null, ?Tenant $tenant = null): string
    {
        if (static::is($tenant)) {
            return asset(static::FAVICON);
        }

        return $site?->nav_logo_url ?? $site?->logo_url ?? asset('img/logo/triangulo.png');
    }
}
