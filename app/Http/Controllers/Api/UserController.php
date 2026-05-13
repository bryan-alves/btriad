<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::query()->with('student')->orderBy('name')->get();

            return response()->json($users, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar usuários',
            ], 500);
        }
    }

    public function show(User $user)
    {
        try {
            return response()->json($user->load('student'), 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $data = $request->validated();
            $studentId = $data['student_id'] ?? null;
            unset($data['student_id']);
            $data['active'] = true;

            $user = DB::transaction(function () use ($data, $studentId) {
                $user = User::create($data);

                if ($studentId) {
                    Student::query()
                        ->whereKey($studentId)
                        ->whereNull('user_id')
                        ->update(['user_id' => $user->id]);
                }

                return $user->load('student');
            });

            return response()->json($user, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao cadastrar usuário',
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $data = $request->validated();
            $studentIdProvided = array_key_exists('student_id', $data);
            $studentId = $data['student_id'] ?? null;
            unset($data['student_id']);

            if (array_key_exists('password', $data) && ($data['password'] === null || $data['password'] === '')) {
                unset($data['password']);
            }

            DB::transaction(function () use ($user, $data, $studentIdProvided, $studentId) {
                if (! empty($data)) {
                    $user->update($data);
                }

                if ($user->role === 'student' && $studentIdProvided) {
                    Student::query()->where('user_id', $user->id)->update(['user_id' => null]);
                    if ($studentId) {
                        Student::query()
                            ->whereKey($studentId)
                            ->whereNull('user_id')
                            ->update(['user_id' => $user->id]);
                    }
                }
            });

            return response()->json($user->fresh()->load('student'), 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar usuário',
            ], 500);
        }
    }
}
