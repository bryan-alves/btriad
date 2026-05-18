<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentGraduationRequest;
use App\Models\Student;
use App\Models\StudentGraduation;
use Illuminate\Http\Request;

class StudentGraduationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = StudentGraduation::with(['student', 'belt'])->orderBy('graduated_at', 'desc');

            if (! $request->has('page')) {
                return response()->json($query->get(), 200);
            }

            $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

            return response()->json($query->paginate($perPage), 200);
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
                'degree' => (int) $data['degree'],
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
                'degree' => (int) $data['degree'],
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

    public function destroy($id)
    {
        try {
            $graduation = StudentGraduation::findOrFail($id);
            $studentId = $graduation->student_id;

            $graduation->delete();

            $latest = StudentGraduation::query()
                ->where('student_id', $studentId)
                ->orderByDesc('graduated_at')
                ->orderByDesc('id')
                ->first();

            Student::query()
                ->whereKey($studentId)
                ->update(['belt_id' => $latest?->belt_id]);

            return response()->json([
                'message' => 'Graduação excluída com sucesso.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Graduação não encontrada.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao excluir graduação.',
            ], 500);
        }
    }
}
