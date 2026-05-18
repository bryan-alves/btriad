<?php

namespace App\Http\Controllers\Api;

use App\Models\AttendanceList;
use App\Models\Student;
use App\Models\StudentGraduation;
use App\Support\AcademyTrainingStats;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Student::query()->with('belt')->orderBy('name');

            if (! $request->boolean('for_user_link')
                && ! $request->boolean('include_inactive')) {
                $query->where('active', true);
            }

            if ($search = trim((string) $request->query('search', ''))) {
                $query->where('name', 'like', '%'.$search.'%');
            }

            if ($request->filled('belt_id')) {
                $query->where('belt_id', (int) $request->query('belt_id'));
            }

            if ($request->has('has_registration_form')) {
                if ($request->boolean('has_registration_form')) {
                    $query->whereNotNull('registration_form_file')
                        ->where('registration_form_file', '!=', '');
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('registration_form_file')
                            ->orWhere('registration_form_file', '');
                    });
                }
            }

            if ($request->has('has_medical_certificate')) {
                if ($request->boolean('has_medical_certificate')) {
                    $query->whereNotNull('medical_certificate')
                        ->where('medical_certificate', '!=', '');
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('medical_certificate')
                            ->orWhere('medical_certificate', '');
                    });
                }
            }

            if ($request->boolean('for_user_link') || $request->boolean('all')) {
                $students = $query->get();

                return response()->json($students, 200);
            }

            $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

            return response()->json($query->paginate($perPage), 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar alunos'
            ], 500);
        }

    }

    public function store(StoreStudentRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')
                    ->store('students', 'public');
            }

            $student = Student::create($data);

            return response()->json($student, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao cadastrar aluno'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $student = Student::with(['belt', 'user'])->findOrFail($id);

            return response()->json($student, 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Aluno não encontrado'
            ], 404);
        }
    }

    public function update(StoreStudentRequest $request, $id)
    {
        try {
            $student = Student::findOrFail($id);

            $data = $request->validated();

            // Atualizar foto (se vier nova)
            if ($request->hasFile('photo')) {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $data['photo'] = $request->file('photo')
                    ->store('students', 'public');
            }

            // Atualizar ficha assinada
            if ($request->hasFile('registration_form_file')) {
                $data['registration_form_file'] = $request
                    ->file('registration_form_file')
                    ->store('students/registration_forms', 'public');
            }

            if (isset($data['address']) && is_string($data['address'])) {
                $data['address'] = json_decode($data['address'], true);
            }

            $student->update($data);

            $student->load('belt');

            return response()->json($student, 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar aluno'
            ], 500);
        }
    }

    /**
     * Listas de presença em que o aluno consta (admin/instrutor).
     */
    public function trainings(Student $student)
    {
        try {
            $lists = AttendanceList::query()
                ->whereHas('students', function ($query) use ($student) {
                    $query->where('students.id', $student->id);
                })
                ->with('schoolClass')
                ->orderByDesc('class_date')
                ->get();

            return response()->json([
                'trainings' => $lists,
                'academy_sessions_by_month' => AcademyTrainingStats::sessionsCountByMonth(),
                'academy_sessions_by_class_month' => AcademyTrainingStats::sessionsCountByClassMonth(),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar treinos do aluno.',
            ], 500);
        }
    }

    /**
     * Graduações do aluno (admin/instrutor).
     */
    public function graduations(Student $student)
    {
        try {
            $rows = StudentGraduation::query()
                ->where('student_id', $student->id)
                ->with('belt')
                ->orderByDesc('graduated_at')
                ->orderByDesc('id')
                ->get();

            return response()->json($rows, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar graduações do aluno.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $student = Student::findOrFail($id);

            $student->update([
                'active' => false
            ]);

            return response()->json([
                'message' => 'Aluno removido com sucesso'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao remover aluno'
            ], 500);
        }
    }
}
