<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformClient extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'website_url',
        'client_tenant_id',
        'sort_order',
        'active',
    ];

    protected $appends = [
        'logo_url',
        'display_website_url',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function clientTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'client_tenant_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            return $this->publicAssetUrl($this->logo_path);
        }

        $this->loadMissing('clientTenant.site');

        return $this->clientTenant?->site?->nav_logo_url
            ?? $this->clientTenant?->site?->logo_url;
    }

    public function getDisplayNameAttribute(): string
    {
        $this->loadMissing('clientTenant');

        return $this->clientTenant?->name ?? $this->name;
    }

    public function getDisplayWebsiteUrlAttribute(): ?string
    {
        if ($this->website_url) {
            return $this->website_url;
        }

        if (! $this->relationLoaded('clientTenant')) {
            return null;
        }

        return $this->clientTenant?->publicWebsiteUrl();
    }

    private function publicAssetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'platform-clients/')) {
            return asset('storage/'.$path);
        }

        return asset(ltrim($path, '/'));
    }
}
