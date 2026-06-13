<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('active');
        });

        DB::table('classes')
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->values()
            ->each(function ($class, int $index) {
                DB::table('classes')
                    ->where('id', $class->id)
                    ->update(['sort_order' => $index]);
            });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
