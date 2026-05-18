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
        return $this->photo
            ? asset('storage/'.$this->photo)
            : null;
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
