<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSite extends Model
{
    protected $fillable = [
        'tenant_id',
        'academy_name',
        'page_title',
        'hero_title',
        'hero_subtitle',
        'primary_color',
        'header_color',
        'background_color',
        'trial_button_color',
        'portal_button_color',
        'app_primary_color',
        'app_header_color',
        'app_background_color',
        'app_login_background_color',
        'logo_path',
        'nav_logo_path',
        'footer_logo_path',
        'hero_logo_path',
        'carousel_images',
        'whatsapp',
        'instagram',
        'youtube',
        'address',
        'schedule',
        'active',
    ];

    protected $appends = [
        'logo_url',
        'nav_logo_url',
        'footer_logo_url',
        'hero_logo_url',
        'carousel_image_urls',
    ];

    protected $casts = [
        'schedule' => 'array',
        'carousel_images' => 'array',
        'active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logoAssetUrl($this->logo_path);
    }

    public function getNavLogoUrlAttribute(): ?string
    {
        return $this->logoAssetUrl($this->nav_logo_path);
    }

    public function getFooterLogoUrlAttribute(): ?string
    {
        return $this->logoAssetUrl($this->footer_logo_path);
    }

    public function getHeroLogoUrlAttribute(): ?string
    {
        return $this->logoAssetUrl($this->hero_logo_path);
    }

    public function getCarouselImageUrlsAttribute(): array
    {
        return collect($this->carousel_images ?? [])
            ->map(fn ($path) => $this->publicAssetUrl($path, 'site-carousel/'))
            ->filter()
            ->values()
            ->all();
    }

    private function logoAssetUrl(?string $path): ?string
    {
        return $this->publicAssetUrl($path, 'site-logos/');
    }

    private function publicAssetUrl(?string $path, string $storagePrefix): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, $storagePrefix)) {
            return asset('storage/'.$path);
        }

        return asset(ltrim($path, '/'));
    }
}
