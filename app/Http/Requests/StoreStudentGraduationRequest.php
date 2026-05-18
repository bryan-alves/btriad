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
            'degree' => ['required', 'integer', 'min:0', 'max:4'],
            'graduated_at' => ['required', 'date'],
            'photo' => ['nullable', 'image', 'max:10240'],
        ];
    }
}
