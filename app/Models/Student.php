<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'belt_id',
        'user_id',
        'photo',
        'name',
        'cpf',
        'birth_date',
        'sex',
        'address',
        'emergency_contacts',
        'other_sports',
        'health_issues',
        'medical_certificate',
        'registration_form_file',
        'active',
        'class_type',
        'degree',
        'first_class_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'first_class_at' => 'date',
        'degree' => 'date',
        'active' => 'boolean',
        'address' => 'array',
        'emergency_contacts' => 'array',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function getAgeAttribute()
    {
        return $this->birth_date?->age;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function belt()
    {
        return $this->belongsTo(Belt::class);
    }
}
