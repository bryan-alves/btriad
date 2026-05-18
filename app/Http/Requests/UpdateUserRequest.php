<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:4'],
            'active' => ['sometimes', 'boolean'],
        ];

        if ($user->role === 'student') {
            $rules['student_id'] = ['nullable', 'integer', 'exists:students,id'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var User|null $user */
            $user = $this->route('user');
            if (! $user) {
                return;
            }

            if ($this->has('active')
                && $this->boolean('active') === false
                && $user->id === $this->user()?->id) {
                $v->errors()->add('active', 'Não é possível desativar a sua própria conta.');
            }

            if ($user->role !== 'student' || ! $this->has('student_id')) {
                return;
            }

            $studentId = $this->input('student_id');
            if (! $studentId) {
                return;
            }

            $student = Student::query()->find($studentId);
            if (! $student) {
                return;
            }
            $alreadyLinkedHere = (int) $student->user_id === (int) $user->id;
            if (! $student->active && ! $alreadyLinkedHere) {
                $v->errors()->add('student_id', 'Só é possível vincular alunos ativos.');
                return;
            }
            if ($student->user_id !== null && ! $alreadyLinkedHere) {
                $v->errors()->add('student_id', 'Este aluno já está vinculado a outro usuário.');
            }
        });
    }
}
