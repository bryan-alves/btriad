<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceListRequest;
use App\Models\AttendanceList;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AttendanceListController extends Controller
{
    public function index()
    {
        try {
            $attendanceLists = AttendanceList::with('students')->orderBy('class_date', 'desc')->get();

            return response()->json($attendanceLists, 200);
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
                'class_type' => $data['class_type'],
                'notes' => $data['notes'] ?? null,
            ]);
            $studentIds = collect($data['student_ids'])->unique()->values()->all();

            if (!empty($studentIds)) {
                $attachments = array_map(function ($studentId) use ($attendanceList) {
                    return [
                        'attendance_list_id' => $attendanceList->id,
                        'student_id' => $studentId,
                        'created_at' => now(),
                    ];
                }, $studentIds);

                DB::table('attendance_list_students')->insert($attachments);

                // Atualizar first_class_at para alunos que não tiverem preenchido
                Student::whereIn('id', $studentIds)
                    ->whereNull('first_class_at')
                    ->update(['first_class_at' => $attendanceList->class_date]);
            }

            $attendanceList->load(['students']);

            return response()->json($attendanceList, 201);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return response()->json([
                'message' => 'Erro ao criar a lista de presença.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $attendanceList = AttendanceList::with('students')->findOrFail($id);

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
                'class_type' => $data['class_type'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Remover estudiantes antigos
            DB::table('attendance_list_students')
                ->where('attendance_list_id', $id)
                ->delete();

            $studentIds = collect($data['student_ids'])->unique()->values()->all();

            if (!empty($studentIds)) {
                $attachments = array_map(function ($studentId) use ($attendanceList) {
                    return [
                        'attendance_list_id' => $attendanceList->id,
                        'student_id' => $studentId,
                        'created_at' => now(),
                    ];
                }, $studentIds);

                DB::table('attendance_list_students')->insert($attachments);
            }

            $attendanceList->load(['students']);

            return response()->json($attendanceList, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar lista de presença.',
            ], 500);
        }
    }
}
