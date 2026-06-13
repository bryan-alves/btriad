<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('students', 'belt_id')) {
            $students = DB::table('students')
                ->whereNotNull('belt_id')
                ->get(['id', 'tenant_id', 'belt_id', 'first_class_at', 'created_at', 'updated_at']);

            foreach ($students as $student) {
                $hasGraduation = DB::table('student_graduations')
                    ->where('student_id', $student->id)
                    ->exists();

                if ($hasGraduation) {
                    continue;
                }

                DB::table('student_graduations')->insert([
                    'tenant_id' => $student->tenant_id,
                    'student_id' => $student->id,
                    'belt_id' => $student->belt_id,
                    'degree' => 0,
                    'graduated_at' => $student->first_class_at ?? ($student->created_at ? substr((string) $student->created_at, 0, 10) : null),
                    'created_at' => $student->created_at ?? now(),
                    'updated_at' => $student->updated_at ?? now(),
                ]);
            }

            Schema::table('students', function (Blueprint $table) {
                $table->dropConstrainedForeignId('belt_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('students', 'belt_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('belt_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        DB::table('students')
            ->leftJoin('student_graduations', function ($join) {
                $join->on('students.id', '=', 'student_graduations.student_id')
                    ->whereRaw('student_graduations.id = (
                        select sg.id
                        from student_graduations sg
                        where sg.student_id = students.id
                        order by sg.graduated_at desc, sg.id desc
                        limit 1
                    )');
            })
            ->whereNotNull('student_graduations.belt_id')
            ->update(['students.belt_id' => DB::raw('student_graduations.belt_id')]);
    }
};
