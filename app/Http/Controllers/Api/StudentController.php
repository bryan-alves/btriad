<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
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

            // Lista completa para vínculo com usuário (inclui inativos; front mostra quem já tem login)
            if (! $request->boolean('for_user_link')) {
                $query->where('active', true);
            }

            $students = $query->get();

            return response()->json($students, 200);
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
            $student = Student::where('active', true)->with('belt')->findOrFail($id);

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
