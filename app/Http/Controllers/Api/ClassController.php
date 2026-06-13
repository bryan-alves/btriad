<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassRequest;
use App\Models\SchoolClass;
use App\Support\ClassScheduleSupport;
use App\Support\SiteScheduleFromClasses;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        try {
            $classes = SchoolClass::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json($classes, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar turmas.',
            ], 500);
        }
    }

    public function schedule()
    {
        try {
            $classes = SchoolClass::query()
                ->where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json(SiteScheduleFromClasses::build($classes), 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar horários.',
            ], 500);
        }
    }

    public function store(StoreClassRequest $request)
    {
        try {
            $data = $request->validated();

            $class = SchoolClass::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'schedule_slots' => ClassScheduleSupport::normalizeSlots($data['schedule_slots']),
                'active' => $data['active'] ?? true,
                'sort_order' => ((int) SchoolClass::query()->max('sort_order')) + 1,
            ]);

            return response()->json($this->classPayload($class), 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao cadastrar turma.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $class = SchoolClass::findOrFail($id);

            return response()->json($this->classPayload($class), 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Turma não encontrada.',
            ], 404);
        }
    }

    public function update(StoreClassRequest $request, $id)
    {
        try {
            $class = SchoolClass::findOrFail($id);

            $data = $request->validated();

            $class->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'schedule_slots' => ClassScheduleSupport::normalizeSlots($data['schedule_slots']),
                'active' => $data['active'] ?? true,
            ]);

            return response()->json($this->classPayload($class->fresh()), 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar turma.',
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'exists:classes,id'],
        ]);

        foreach ($data['order'] as $index => $classId) {
            SchoolClass::query()
                ->whereKey($classId)
                ->update(['sort_order' => $index]);
        }

        return response()->json([
            'message' => 'Ordem das turmas atualizada.',
        ], 200);
    }

    private function classPayload(SchoolClass $class): array
    {
        $payload = $class->toArray();
        $payload['schedule_conflicts'] = ClassScheduleSupport::detectConflicts(
            $class->schedule_slots,
            SchoolClass::query()->orderBy('sort_order')->orderBy('name')->get(),
            (int) $class->id,
        );

        return $payload;
    }
}
