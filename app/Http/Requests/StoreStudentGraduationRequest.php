<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentGraduationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'belt_id' => ['required', 'integer', 'exists:belts,id'],
            'degree' => ['nullable', 'string', 'max:255'],
            'graduated_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
