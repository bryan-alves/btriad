<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceList;
use App\Models\SiteReview;
use App\Support\AcademyTrainingStats;
use App\Support\CurrentTenant;
use App\Models\StudentGraduation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function canManageSites(User $user, Request $request): bool
    {
        return $user->role === 'admin';
    }

    private function userPayload(User $user, Request $request): array
    {
        $user->load(['student.currentGraduation.belt']);

        return array_merge($user->toArray(), [
            'tenant' => CurrentTenant::get(),
            'can_manage_sites' => $this->canManageSites($user, $request),
        ]);
    }

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

        return response()->json([
            'token' => $user->createToken('auth-token')->plainTextToken,
            'user' => $this->userPayload($user, $request),
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
        return response()->json($this->userPayload($request->user(), $request), 200);
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
            'photo' => ['required', 'image', 'max:5120'],
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

        return response()->json($this->userPayload($user, $request), 200);
    }

    /**
     * Avaliação do site enviada pelo aluno (se existir).
     */
    public function studentReview(Request $request)
    {
        $user = $request->user()->load('student');

        if (! $user->student) {
            return response()->json(null, 200);
        }

        $review = SiteReview::query()
            ->where('student_id', $user->student->id)
            ->first();

        return response()->json($review, 200);
    }

    /**
     * Cria ou atualiza a avaliação do site pelo aluno (fica pendente de aprovação).
     */
    public function storeStudentReview(Request $request)
    {
        $user = $request->user()->load('student');

        if (! $user->student) {
            return response()->json([
                'message' => 'Nenhum aluno vinculado a esta conta.',
            ], 422);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:'.SiteReview::MAX_COMMENT_LENGTH],
        ]);

        $student = $user->student;
        $existing = SiteReview::query()
            ->where('student_id', $student->id)
            ->first();

        if ($existing && $existing->status === SiteReview::STATUS_APPROVED) {
            return response()->json([
                'message' => 'Sua avaliação já foi publicada no site.',
            ], 422);
        }

        $review = SiteReview::query()->updateOrCreate(
            ['student_id' => $student->id],
            [
                'author_name' => $student->name,
                'author_photo_path' => $student->photo,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
                'status' => SiteReview::STATUS_PENDING,
                'active' => false,
                'sort_order' => $existing?->sort_order ?? 0,
            ],
        );

        return response()->json($review->fresh(), $existing ? 200 : 201);
    }
}
