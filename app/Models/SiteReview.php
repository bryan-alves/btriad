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
        'author_photo_path',
        'rating',
        'comment',
        'active',
        'sort_order',
    ];

    protected $appends = [
        'author_photo_url',
    ];

    protected $casts = [
        'active' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];

    public function getAuthorPhotoUrlAttribute(): ?string
    {
        $path = $this->author_photo_path;

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'site-review-photos/')) {
            return asset('storage/'.$path);
        }

        return asset(ltrim($path, '/'));
    }
}
