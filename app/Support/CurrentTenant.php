<?php

namespace App\Support;

use App\Models\Tenant;

class CurrentTenant
{
    public static function get(): ?Tenant
    {
        if (! app()->bound('tenant')) {
            return null;
        }

        return app('tenant');
    }

    public static function id(): ?int
    {
        return static::get()?->id;
    }
}
