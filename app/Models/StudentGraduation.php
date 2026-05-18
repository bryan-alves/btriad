<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGraduation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'belt_id',
        'degree',
        'graduated_at',
        'photo',
    ];

    protected $casts = [
        'degree' => 'integer',
        'graduated_at' => 'date',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
            return $this->photo;
        }

        return asset('storage/'.$this->photo);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function belt()
    {
        return $this->belongsTo(Belt::class);
    }
}
