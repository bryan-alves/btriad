<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }
}
