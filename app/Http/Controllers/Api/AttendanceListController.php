<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceListRequest;
use App\Models\AttendanceList;
use App\Models\Student;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceListController extends Controller
{
    public function rankingPeriods()
    {
        try {
            $pairs = DB::table('attendance_lists')
                ->join('attendance_list_students', 'attendance_list_students.attendance_list_id', '=', 'attendance_lists.id')
                ->join('students', 'students.id', '=', 'attendance_list_students.student_id')
                ->where('attendance_lists.tenant_id', CurrentTenant::id())
                ->where('attendance_list_students.tenant_id', CurrentTenant::id())
                ->where('students.tenant_id', CurrentTenant::id())
                ->where('students.active', true)
                ->selectRaw('YEAR(attendance_lists.class_date) as year, MONTH(attendance_lists.class_date) as month')
                ->distinct()
                ->orderByDesc('year')
                ->orderBy('month')
                ->get();

            $monthsByYear = [];
            $years = [];

            foreach ($pairs as $row) {
                $year = (int) $row->year;
                $month = (int) $row->month;
                if ($month < 1 || $month > 12) {
                    continue;
                }
                $years[$year] = true;
                $monthsByYear[$year] ??= [];
                $monthsByYear[$year][$month] = true;
            }

            $yearList = array_keys($years);
            rsort($yearList);

            $monthsByYearFormatted = [];
            foreach ($monthsByYear as $year => $months) {
                $monthList = array_keys($months);
                sort($monthList, SORT_NUMERIC);
                $monthsByYearFormatted[$year] = $monthList;
            }

            return response()->json([
                'years' => $yearList,
                'months_by_year' => $monthsByYearFormatted,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar períodos do ranking.',
            ], 500);
        }
    }

    public function ranking(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => ['required', 'integer', 'min:2000', 'max:2100'],
                'month' => ['required', 'integer', Rule::in(array_merge([0], range(1, 12)))],
                'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            ]);

            $year = (int) $validated['year'];
            $month = (int) $validated['month'];
            $classId = isset($validated['class_id']) ? (int) $validated['class_id'] : null;

            $listsQuery = AttendanceList::query()
                ->with(['students' => fn ($q) => $q->where('students.active', true)])
                ->whereYear('class_date', $year)
                ->whereHas('students', fn ($q) => $q->where('students.active', true));

            if ($month > 0) {
                $listsQuery->whereMonth('class_date', $month);
            }

            if ($classId) {
                $listsQuery->where('class_id', $classId);
            }

            $lists = $listsQuery->get();

            $byStudent = [];

            foreach ($lists as $list) {
                $dateKey = $list->class_date->toDateString();
                $classKey = (string) ($list->class_id ?? 0);

                foreach ($list->students as $student) {
                    if (! $student->active) {
                        continue;
                    }

                    if (! isset($byStudent[$student->id])) {
                        $byStudent[$student->id] = [
                            'id' => $student->id,
                            'name' => $student->name,
                            'sessions' => [],
                        ];
                    }

                    // Turma específica: um treino por dia. Todos: soma por turma (mesmo dia em turmas diferentes conta).
                    $sessionKey = $classId
                        ? $dateKey
                        : $dateKey.'|'.$classKey;

                    $byStudent[$student->id]['sessions'][$sessionKey] = true;
                }
            }

            $ranking = collect($byStudent)
                ->map(fn (array $row) => [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'count' => count($row['sessions']),
                ])
                ->sort(function (array $a, array $b) {
                    if ($b['count'] !== $a['count']) {
                        return $b['count'] <=> $a['count'];
                    }

                    return strcasecmp($a['name'], $b['name']);
                })
                ->values()
                ->all();

            return response()->json([
                'ranking' => $ranking,
                'has_training' => $lists->isNotEmpty(),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar ranking.',
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = AttendanceList::with(['students', 'schoolClass'])->orderBy('class_date', 'desc');

            if ($request->filled('date_from')) {
                $query->whereDate('class_date', '>=', $request->query('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('class_date', '<=', $request->query('date_to'));
            }

            if ($request->filled('class_type')) {
                $type = $request->query('class_type');
                if (in_array($type, ['adult', 'kids'], true)) {
                    $query->whereHas('schoolClass', fn ($q) => $q->where('type', $type));
                }
            }

            if (! $request->has('page')) {
                return response()->json($query->get(), 200);
            }

            $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

            return response()->json($query->paginate($perPage), 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar listas de presença.',
            ], 500);
        }
    }

    public function store(StoreAttendanceListRequest $request)
    {
        try {
            $data = $request->validated();

            $attendanceList = AttendanceList::create([
                'class_date' => $data['class_date'],
                'class_id' => $data['class_id'],
                'notes' => $data['notes'] ?? null,
            ]);
            $studentIds = collect($data['student_ids'])->unique()->values()->all();

            if (!empty($studentIds)) {
                $attachments = array_map(function ($studentId) use ($attendanceList) {
                    return [
                        'attendance_list_id' => $attendanceList->id,
                        'student_id' => $studentId,
                        'tenant_id' => CurrentTenant::id(),
                        'created_at' => now(),
                    ];
                }, $studentIds);

                DB::table('attendance_list_students')->insert($attachments);

                // Atualizar first_class_at para alunos que não tiverem preenchido
                Student::whereIn('id', $studentIds)
                    ->whereNull('first_class_at')
                    ->update(['first_class_at' => $attendanceList->class_date]);
            }

            $attendanceList->load(['students', 'schoolClass']);

            return response()->json($attendanceList, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao criar a lista de presença.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $attendanceList = AttendanceList::with(['students', 'schoolClass'])->findOrFail($id);

            return response()->json($attendanceList, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Lista de presença não encontrada.',
            ], 404);
        }
    }

    public function update(StoreAttendanceListRequest $request, $id)
    {
        try {
            $attendanceList = AttendanceList::findOrFail($id);
            $data = $request->validated();

            $attendanceList->update([
                'class_date' => $data['class_date'],
                'class_id' => $data['class_id'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Remover estudiantes antigos
            DB::table('attendance_list_students')
                ->where('attendance_list_id', $id)
                ->where('tenant_id', CurrentTenant::id())
                ->delete();

            $studentIds = collect($data['student_ids'])->unique()->values()->all();

            if (!empty($studentIds)) {
                $attachments = array_map(function ($studentId) use ($attendanceList) {
                    return [
                        'attendance_list_id' => $attendanceList->id,
                        'student_id' => $studentId,
                        'tenant_id' => CurrentTenant::id(),
                        'created_at' => now(),
                    ];
                }, $studentIds);

                DB::table('attendance_list_students')->insert($attachments);
            }

            $attendanceList->load(['students', 'schoolClass']);

            return response()->json($attendanceList, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar lista de presença.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $attendanceList = AttendanceList::findOrFail($id);
            $attendanceList->delete();

            return response()->json([
                'message' => 'Treino excluído com sucesso.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao excluir treino.',
            ], 500);
        }
    }
}
