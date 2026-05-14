<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Move o tipo de turma (kids/adult) para `classes` e remove de `attendance_lists`.
     */
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->string('type')->default('kids');
        });

        $lists = DB::table('attendance_lists')->orderBy('id')->get();
        $byClass = [];
        foreach ($lists as $row) {
            $byClass[(int) $row->class_id] = $row->class_type;
        }
        foreach ($byClass as $classId => $classType) {
            if (in_array($classType, ['kids', 'adult'], true)) {
                DB::table('classes')->where('id', $classId)->update(['type' => $classType]);
            }
        }

        Schema::table('attendance_lists', function (Blueprint $table) {
            $table->dropColumn('class_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_lists', function (Blueprint $table) {
            $table->string('class_type')->default('kids');
        });

        $lists = DB::table('attendance_lists')->get();
        foreach ($lists as $row) {
            $type = DB::table('classes')->where('id', $row->class_id)->value('type');
            if ($type) {
                DB::table('attendance_lists')->where('id', $row->id)->update(['class_type' => $type]);
            }
        }

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
