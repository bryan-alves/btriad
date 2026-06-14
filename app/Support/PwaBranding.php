<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\TenantSite;

class PwaBranding
{
    public static function shortName(?Tenant $tenant, ?TenantSite $site): string
    {
        if (TatameiroBranding::is($tenant)) {
            return 'Tatameiro';
        }

        $name = trim($site?->academy_name ?? $tenant?->name ?? 'Academia');

        if (preg_match('/\bb[\s-]?triad\b/i', $name)) {
            return 'B-Triad';
        }

        if (mb_strlen($name) <= 14) {
            return $name;
        }

        return mb_substr($name, 0, 14);
    }
}
