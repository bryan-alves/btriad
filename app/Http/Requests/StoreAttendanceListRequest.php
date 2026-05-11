<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_date' => ['required', 'date'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'class_type' => ['required', 'string', 'in:kids,adult'],
            'notes' => ['nullable', 'string'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
        ];
    }
}
