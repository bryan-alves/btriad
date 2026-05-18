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
}
