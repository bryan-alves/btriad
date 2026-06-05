<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
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
}
