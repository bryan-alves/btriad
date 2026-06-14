<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    public const PLAN_APP = 'app';

    public const PLAN_DIGITAL = 'digital';

    protected $fillable = [
        'name',
        'slug',
        'plan',
        'is_platform',
    ];

    protected $casts = [
        'is_platform' => 'boolean',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function site(): HasOne
    {
        return $this->hasOne(TenantSite::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SiteReview::class);
    }

    public function hasPublicSite(): bool
    {
        if ($this->is_platform) {
            return true;
        }

        return $this->plan === self::PLAN_DIGITAL;
    }

    public function isAppPlan(): bool
    {
        return $this->plan === self::PLAN_APP;
    }

    public static function planLabels(): array
    {
        return [
            self::PLAN_APP => 'Tatameiro App',
            self::PLAN_DIGITAL => 'Academia Digital',
        ];
    }
}
