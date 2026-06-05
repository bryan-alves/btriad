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
        'whatsapp',
        'instagram',
        'address',
        'schedule',
        'active',
    ];

    protected $appends = [
        'logo_url',
    ];

    protected $casts = [
        'schedule' => 'array',
        'active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        if (str_starts_with($this->logo_path, 'site-logos/')) {
            return asset('storage/'.$this->logo_path);
        }

        return asset(ltrim($this->logo_path, '/'));
    }
}
