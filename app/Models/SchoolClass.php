<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\ClassScheduleSupport;
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
        'schedule_slots',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'schedule_slots' => 'array',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'schedule_summary',
    ];

    public function getScheduleSummaryAttribute(): string
    {
        return ClassScheduleSupport::summarizeSlots($this->schedule_slots);
    }
}
