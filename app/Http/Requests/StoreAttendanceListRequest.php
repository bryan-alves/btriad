<?php

namespace App\Http\Requests;

use App\Support\CurrentTenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'class_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->where(fn ($query) => $query->where('tenant_id', CurrentTenant::id())),
            ],
            'photo' => ['nullable', 'string', 'max:500'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', CurrentTenant::id())),
            ],
        ];
    }
}
