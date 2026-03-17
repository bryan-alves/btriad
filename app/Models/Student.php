<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cpf',
        'birth_date',
        'active',
        'photo'
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
}
