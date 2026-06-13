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
            $table->json('schedule_slots')->nullable()->after('type');
        });

        DB::table('classes')->orderBy('id')->each(function (object $class) {
            $slots = [];
            $hasWeekday = Schema::hasColumn('classes', 'weekday');

            if (! empty($class->start_time) && ($hasWeekday ? ! empty($class->weekday) : true)) {
                $slot = [
                    'start_time' => substr((string) $class->start_time, 0, 5),
                    'end_time' => $class->end_time ? substr((string) $class->end_time, 0, 5) : null,
                ];

                if ($hasWeekday && ! empty($class->weekday)) {
                    $slot['weekday'] = (int) $class->weekday;
                } else {
                    $slot['weekday'] = 1;
                }

                $slots[] = $slot;
            }

            DB::table('classes')->where('id', $class->id)->update([
                'schedule_slots' => json_encode($slots),
            ]);
        });

        Schema::table('classes', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('classes', 'weekday') ? 'weekday' : null,
                Schema::hasColumn('classes', 'start_time') ? 'start_time' : null,
                Schema::hasColumn('classes', 'end_time') ? 'end_time' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedTinyInteger('weekday')->nullable()->after('type');
            $table->time('start_time')->nullable()->after('weekday');
            $table->time('end_time')->nullable()->after('start_time');
        });

        DB::table('classes')->orderBy('id')->each(function (object $class) {
            $slots = json_decode((string) ($class->schedule_slots ?? '[]'), true) ?: [];
            $first = $slots[0] ?? null;

            DB::table('classes')->where('id', $class->id)->update([
                'weekday' => $first['weekday'] ?? null,
                'start_time' => isset($first['start_time']) ? $first['start_time'].':00' : null,
                'end_time' => isset($first['end_time']) ? $first['end_time'].':00' : null,
            ]);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('schedule_slots');
        });
    }
};
