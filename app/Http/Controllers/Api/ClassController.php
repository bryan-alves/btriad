<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassRequest;
use App\Models\SchoolClass;

class ClassController extends Controller
{
    public function index()
    {
        try {
            $classes = SchoolClass::orderBy('name')->get();

            return response()->json($classes, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar turmas.',
            ], 500);
        }
    }

    public function store(StoreClassRequest $request)
    {
        try {
            $data = $request->validated();

            $class = SchoolClass::create([
                'name' => $data['name'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            return response()->json($class, 201);
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

            return response()->json($class, 200);
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
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            return response()->json($class, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar turma.',
            ], 500);
        }
    }
}
