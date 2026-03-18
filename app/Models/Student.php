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
        'practices_other_sports',
        'other_sports',
        'health_issues',
        'medical_certificate',
        'image_authorization',
        'image_authorization_file',
        'active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'active' => 'boolean',
        'address' => 'array',
        'emergency_contacts' => 'array',
        'practices_other_sports' => 'boolean',
        'image_authorization' => 'boolean',
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
