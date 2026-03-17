<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'user_id' => ['nullable', 'exists:users,id'],

            // Dados básicos
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'size:14', 'unique:students,cpf'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'sex' => ['nullable', 'string', 'in:M,F,outro'],

            // Arquivo
            'photo' => ['nullable', 'image', 'max:2048'],

            // JSONs
            'address' => ['nullable', 'array'],
            'emergency_contacts' => ['nullable', 'array'],

            // Esportes
            'practices_other_sports' => ['boolean'],
            'other_sports' => ['nullable', 'string', 'max:255', 'required_if:practices_other_sports,true'],

            // Saúde
            'health_issues' => ['nullable', 'string'],
            'medical_certificate' => ['nullable', 'string', 'max:255'],

            // Autorização de imagem
            'image_authorization' => ['boolean'],
            'image_authorization_file' => ['nullable', 'string', 'max:255', 'required_if:image_authorization,true'],

            // Status
            'active' => ['boolean'],
        ];
    }
}