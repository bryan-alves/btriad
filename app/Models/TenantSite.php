<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSite extends Model
{
    protected $fillable = [
        'tenant_id',
        'academy_name',
        'primary_color',
        'logo_path',
        'whatsapp',
        'instagram',
        'address',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
