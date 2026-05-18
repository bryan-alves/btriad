<?php

namespace App\Support;

use App\Models\AttendanceList;
use Illuminate\Support\Facades\DB;

class AcademyTrainingStats
{
    /**
     * Total de listas de presença (aulas) por mês na academia — chave YYYY-MM.
     *
     * @return array<string, int>
     */
    public static function sessionsCountByMonth(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = AttendanceList::query()
                ->selectRaw("strftime('%Y-%m', class_date) as month_key, COUNT(*) as total")
                ->groupBy('month_key')
                ->orderBy('month_key')
                ->get();
        } else {
            $rows = AttendanceList::query()
                ->selectRaw('DATE_FORMAT(class_date, "%Y-%m") as month_key, COUNT(*) as total')
                ->groupBy('month_key')
                ->orderBy('month_key')
                ->get();
        }

        $out = [];
        foreach ($rows as $row) {
            if ($row->month_key) {
                $out[$row->month_key] = (int) $row->total;
            }
        }

        return $out;
    }

    /**
     * Total de aulas por turma (class_id) e mês — chave externa class_id, interna YYYY-MM.
     *
     * @return array<string, array<string, int>>
     */
    public static function sessionsCountByClassMonth(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = AttendanceList::query()
                ->whereNotNull('class_id')
                ->selectRaw("class_id, strftime('%Y-%m', class_date) as month_key, COUNT(*) as total")
                ->groupBy('class_id', 'month_key')
                ->orderBy('class_id')
                ->orderBy('month_key')
                ->get();
        } else {
            $rows = AttendanceList::query()
                ->whereNotNull('class_id')
                ->selectRaw('class_id, DATE_FORMAT(class_date, "%Y-%m") as month_key, COUNT(*) as total')
                ->groupBy('class_id', 'month_key')
                ->orderBy('class_id')
                ->orderBy('month_key')
                ->get();
        }

        $out = [];
        foreach ($rows as $row) {
            if (! $row->class_id || ! $row->month_key) {
                continue;
            }
            $classKey = (string) $row->class_id;
            $out[$classKey] ??= [];
            $out[$classKey][$row->month_key] = (int) $row->total;
        }

        return $out;
    }
}
