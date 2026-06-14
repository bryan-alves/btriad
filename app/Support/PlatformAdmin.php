<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;

class PlatformAdmin
{
    public static function is(?User $user): bool
    {
        if (! $user || $user->role !== 'admin') {
            return false;
        }

        $tenant = Tenant::query()
            ->whereKey($user->tenant_id)
            ->first();

        return (bool) ($tenant?->is_platform);
    }

    public static function authorize(?User $user): void
    {
        if (! static::is($user)) {
            abort(403, 'Acesso restrito ao painel Tatameiro.');
        }
    }
}
