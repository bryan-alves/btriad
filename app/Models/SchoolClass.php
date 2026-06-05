<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'start_time',
        'end_time',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
