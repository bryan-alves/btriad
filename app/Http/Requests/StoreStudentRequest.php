<?php

namespace App\Http\Requests;

use App\Support\CurrentTenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Relacionamento
            'belt_id' => ['nullable', 'exists:belts,id'],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', CurrentTenant::id())),
            ],

            // Dados básicos
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'size:11'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'sex' => ['nullable', 'string', 'in:M,F,outro'],

            // Arquivo
            'photo' => ['nullable', 'image', 'max:2048'],

            // JSONs
            'address' => ['nullable'],
            'emergency_contacts' => ['nullable'],

            // Esportes
            'other_sports' => ['nullable', 'string', 'max:255'],

            // Saúde
            'health_issues' => ['nullable', 'string'],
            'medical_certificate' => ['nullable', 'string', 'max:255'],

            'registration_form_file' => ['nullable', 'string', 'max:255'],

            'class_type' => ['in:kids,adult'],
            // Status
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'address' => json_decode($this->address, true),
            'emergency_contacts' => json_decode($this->emergency_contacts, true),
        ]);
    }
}