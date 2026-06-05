<?php

namespace App\Http\Requests;

use App\Support\CurrentTenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentGraduationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', CurrentTenant::id())),
            ],
            'belt_id' => ['required', 'integer', 'exists:belts,id'],
            'degree' => ['required', 'integer', 'min:0', 'max:4'],
            'graduated_at' => ['required', 'date'],
            'photo' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
