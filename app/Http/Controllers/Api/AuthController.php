<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceList;
use App\Support\AcademyTrainingStats;
use App\Models\StudentGraduation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'username' => ['Esta conta está desativada.'],
            ]);
        }

        $user->load(['student.belt']);

        return response()->json([
            'token' => $user->createToken('auth-token')->plainTextToken,
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Desconectado com sucesso.',
        ], 200);
    }

    public function user(Request $request)
    {
        return response()->json($request->user()->load(['student.belt']), 200);
    }

    /**
     * Listas de presença em que o aluno vinculado ao usuário aparece (somente leitura).
     */
    public function studentTrainings(Request $request)
    {
        $user = $request->user()->load('student');

        if (! $user->student) {
            return response()->json([
                'trainings' => [],
                'academy_sessions_by_month' => AcademyTrainingStats::sessionsCountByMonth(),
                'academy_sessions_by_class_month' => AcademyTrainingStats::sessionsCountByClassMonth(),
            ], 200);
        }

        $studentId = $user->student->id;

        $lists = AttendanceList::query()
            ->whereHas('students', function ($query) use ($studentId) {
                $query->where('students.id', $studentId);
            })
            ->with('schoolClass')
            ->orderByDesc('class_date')
            ->get();

        return response()->json([
            'trainings' => $lists,
            'academy_sessions_by_month' => AcademyTrainingStats::sessionsCountByMonth(),
            'academy_sessions_by_class_month' => AcademyTrainingStats::sessionsCountByClassMonth(),
        ], 200);
    }

    /**
     * Graduações registradas para o aluno vinculado ao usuário (somente leitura).
     */
    public function studentGraduations(Request $request)
    {
        $user = $request->user()->load('student');

        if (! $user->student) {
            return response()->json([], 200);
        }

        $rows = StudentGraduation::query()
            ->where('student_id', $user->student->id)
            ->with('belt')
            ->orderByDesc('graduated_at')
            ->orderByDesc('id')
            ->get();

        return response()->json($rows, 200);
    }

    /**
     * Atualiza apenas a foto do aluno vinculado ao usuário autenticado.
     */
    public function updateStudentPhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user()->load('student');

        if (! $user->student) {
            return response()->json([
                'message' => 'Nenhum aluno vinculado a esta conta.',
            ], 422);
        }

        $student = $user->student;

        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $path = $request->file('photo')->store('students', 'public');
        $student->update(['photo' => $path]);

        $user->load(['student.belt']);

        return response()->json($user, 200);
    }
}
