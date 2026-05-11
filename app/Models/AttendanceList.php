<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_date',
        'class_type',
        'notes',
    ];

    protected $casts = [
        'class_date' => 'date',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'attendance_list_students', 'attendance_list_id', 'student_id')
            ->withPivot('created_at');
    }
}
