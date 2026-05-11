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
        'notes',
    ];

    protected $casts = [
        'graduated_at' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function belt()
    {
        return $this->belongsTo(Belt::class);
    }
}
