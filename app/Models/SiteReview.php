<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SiteReview extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'author_name',
        'rating',
        'comment',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];
}
