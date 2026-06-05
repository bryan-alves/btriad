<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Support\CurrentTenant;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'role' => 'student',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->where(fn ($query) => $query->where('tenant_id', CurrentTenant::id())),
            ],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:student'],
            'student_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', CurrentTenant::id())),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $studentId = $this->input('student_id');
            if (! $studentId) {
                return;
            }

            $student = Student::query()->find($studentId);
            if (! $student) {
                return;
            }
            if (! $student->active) {
                $v->errors()->add('student_id', 'Só é possível vincular alunos ativos.');
                return;
            }
            if ($student->user_id !== null) {
                $v->errors()->add('student_id', 'Este aluno já está vinculado a outro usuário.');
            }
        });
    }
}
