<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('student_graduations')->orderBy('id')->cursor() as $row) {
            DB::table('student_graduations')->where('id', $row->id)->update([
                'degree' => $this->normalizeDegree($row->degree),
            ]);
        }

        Schema::table('student_graduations', function (Blueprint $table) {
            $table->unsignedTinyInteger('degree')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_graduations', function (Blueprint $table) {
            $table->string('degree')->nullable()->change();
        });
    }

    private function normalizeDegree(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            return max(0, min(4, (int) $value));
        }
        if (preg_match('/(\d)/', (string) $value, $m)) {
            return max(0, min(4, (int) $m[1]));
        }

        return 0;
    }
};
