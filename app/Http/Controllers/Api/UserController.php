<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::with('student')->get();

            return response()->json($users, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar usuários',
            ], 500);
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $data = $request->validated();
            $studentId = $data['student_id'] ?? null;
            unset($data['student_id']);

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
}
