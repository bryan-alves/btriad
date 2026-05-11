<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:admin,instructor,student'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $studentId = $this->input('student_id');
            if (! $studentId) {
                return;
            }

            if ($this->input('role') !== 'student') {
                $v->errors()->add('student_id', 'Só é possível vincular um cadastro de aluno quando o perfil for Aluno.');

                return;
            }

            $student = Student::query()->find($studentId);
            if ($student && $student->user_id !== null) {
                $v->errors()->add('student_id', 'Este aluno já está vinculado a outro usuário.');
            }
        });
    }
}
