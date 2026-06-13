<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteReview extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'author_name',
        'author_photo_path',
        'rating',
        'comment',
        'status',
        'active',
        'sort_order',
    ];

    protected $appends = [
        'author_photo_url',
        'short_author_name',
    ];

    protected $casts = [
        'active' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getShortAuthorNameAttribute(): string
    {
        $parts = preg_split('/\s+/u', trim((string) $this->author_name), -1, PREG_SPLIT_NO_EMPTY);

        return implode(' ', array_slice($parts, 0, 2));
    }

    public function getAuthorPhotoUrlAttribute(): ?string
    {
        $path = $this->author_photo_path;

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'site-review-photos/') || str_starts_with($path, 'students/')) {
            return asset('storage/'.$path);
        }

        return asset(ltrim($path, '/'));
    }
}
