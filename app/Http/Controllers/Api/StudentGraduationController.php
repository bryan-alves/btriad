<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentGraduationRequest;
use App\Models\StudentGraduation;

class StudentGraduationController extends Controller
{
    public function index()
    {
        try {
            $graduations = StudentGraduation::with(['student', 'belt'])->orderBy('graduated_at', 'desc')->get();

            return response()->json($graduations, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar graduações.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $graduation = StudentGraduation::with(['student', 'belt'])->findOrFail($id);

            return response()->json($graduation, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Graduação não encontrada.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar graduação.',
            ], 500);
        }
    }

    public function update(StoreStudentGraduationRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $graduation = StudentGraduation::findOrFail($id);

            $graduation->update([
                'student_id' => $data['student_id'],
                'belt_id' => $data['belt_id'],
                'degree' => $data['degree'] ?? null,
                'graduated_at' => $data['graduated_at'],
                'notes' => $data['notes'] ?? null,
            ]);

            $graduation->student()->update(['belt_id' => $graduation->belt_id]);
            $graduation->load(['student', 'belt']);

            return response()->json($graduation, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar graduação.',
            ], 500);
        }
    }

    public function store(StoreStudentGraduationRequest $request)
    {
        try {
            $data = $request->validated();

            $graduation = StudentGraduation::create([
                'student_id' => $data['student_id'],
                'belt_id' => $data['belt_id'],
                'degree' => $data['degree'] ?? null,
                'graduated_at' => $data['graduated_at'],
                'notes' => $data['notes'] ?? null,
            ]);

            $graduation->student()->update(['belt_id' => $graduation->belt_id]);
            $graduation->load(['student', 'belt']);

            return response()->json($graduation, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao cadastrar graduação do aluno.',
            ], 500);
        }
    }
}
