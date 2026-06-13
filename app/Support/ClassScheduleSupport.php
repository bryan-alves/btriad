<?php

namespace App\Support;

use App\Models\SchoolClass;
use Illuminate\Support\Collection;

class ClassScheduleSupport
{
    /** ISO-8601: 1 = segunda … 7 = domingo */
    public const WEEKDAY_LABELS = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public const WEEKDAY_SHORT = [
        1 => 'Seg',
        2 => 'Ter',
        3 => 'Qua',
        4 => 'Qui',
        5 => 'Sex',
        6 => 'Sáb',
        7 => 'Dom',
    ];

    /** Segunda a sexta (ISO-8601). */
    public const WEEKDAY_RANGE = [1, 2, 3, 4, 5];

    public const WEEKDAY_HEADERS = [
        1 => 'Segunda',
        2 => 'Terça',
        3 => 'Quarta',
        4 => 'Quinta',
        5 => 'Sexta',
    ];

    /**
     * @param  array<int, array{weekday?: int|string|null, start_time?: string|null, end_time?: string|null}>|null  $slots
     * @return array<int, array{weekday: int, start_time: string, end_time: ?string}>
     */
    public static function normalizeSlots(?array $slots): array
    {
        return collect($slots ?? [])
            ->map(function ($slot) {
                if (! is_array($slot)) {
                    return null;
                }

                $weekday = (int) ($slot['weekday'] ?? 0);
                $startTime = self::normalizeTime($slot['start_time'] ?? null);

                if ($weekday < 1 || $weekday > 7 || ! $startTime) {
                    return null;
                }

                return [
                    'weekday' => $weekday,
                    'start_time' => $startTime,
                    'end_time' => self::normalizeTime($slot['end_time'] ?? null),
                ];
            })
            ->filter()
            ->sortBy(fn (array $slot) => sprintf('%02d-%s', $slot['weekday'], $slot['start_time']))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, SchoolClass>|iterable<SchoolClass>  $classes
     * @return array{
     *     weekdays: array<int, array{weekday: int, label: string}>,
     *     rows: array<int, array{class_name: string, times: array<int, array<int, string>>}>
     * }
     */
    public static function buildPublicSchedule(iterable $classes): array
    {
        $weekdays = self::WEEKDAY_RANGE;
        $weekdayHeaders = array_map(
            fn (int $day) => [
                'weekday' => $day,
                'label' => self::WEEKDAY_HEADERS[$day] ?? '',
            ],
            $weekdays,
        );

        $rows = [];
        $activeClasses = collect($classes)
            ->filter(fn (SchoolClass $class) => $class->active)
            ->sortBy([
                fn (SchoolClass $class) => $class->sort_order ?? 0,
                fn (SchoolClass $class) => mb_strtolower($class->name),
            ]);

        foreach ($activeClasses as $class) {
            $slotsByDay = [];

            foreach (self::normalizeSlots($class->schedule_slots) as $slot) {
                if (! in_array($slot['weekday'], $weekdays, true)) {
                    continue;
                }

                $slotsByDay[$slot['weekday']][] = self::formatSlotRange(
                    $slot['start_time'],
                    $slot['end_time'],
                );
            }

            if ($slotsByDay === []) {
                continue;
            }

            $times = [];
            foreach ($weekdays as $day) {
                $times[] = isset($slotsByDay[$day])
                    ? collect($slotsByDay[$day])->unique()->values()->all()
                    : [];
            }

            $rows[] = [
                'class_id' => (int) $class->id,
                'class_name' => (string) $class->name,
                'times' => $times,
            ];
        }

        return [
            'weekdays' => $weekdayHeaders,
            'rows' => $rows,
        ];
    }

    public static function formatSlotRange(string $startTime, ?string $endTime): string
    {
        $start = self::formatDisplayTime($startTime);
        $end = $endTime ? self::formatDisplayTime($endTime) : null;

        if ($start && $end) {
            return "{$start} - {$end}";
        }

        return $start ?: '-';
    }

    /**
     * @param  array<int, array{weekday: int, start_time: string, end_time: ?string}>  $slots
     * @param  Collection<int, SchoolClass>  $allClasses
     * @return array<int, array{slot_index: int, class_id: int, class_name: string, message: string}>
     */
    public static function detectConflicts(?array $slots, Collection $allClasses, ?int $ignoreClassId = null): array
    {
        $normalizedSlots = self::normalizeSlots($slots);
        $warnings = [];

        foreach ($normalizedSlots as $index => $slot) {
            foreach ($normalizedSlots as $otherIndex => $otherSlot) {
                if ($otherIndex <= $index) {
                    continue;
                }

                if (! self::slotsOverlap($slot, $otherSlot)) {
                    continue;
                }

                $warnings[] = [
                    'slot_index' => $index,
                    'class_id' => (int) ($ignoreClassId ?? 0),
                    'class_name' => '',
                    'message' => sprintf(
                        'Conflito entre horários desta turma em %s (%s).',
                        self::WEEKDAY_LABELS[$slot['weekday']] ?? '',
                        self::formatSlotRange($slot['start_time'], $slot['end_time']),
                    ),
                ];
            }

            foreach ($allClasses as $otherClass) {
                if ($ignoreClassId && (int) $otherClass->id === $ignoreClassId) {
                    continue;
                }

                if (! $otherClass->active) {
                    continue;
                }

                foreach (self::normalizeSlots($otherClass->schedule_slots) as $otherSlot) {
                    if (! self::slotsOverlap($slot, $otherSlot)) {
                        continue;
                    }

                    $warnings[] = [
                        'slot_index' => $index,
                        'class_id' => (int) $otherClass->id,
                        'class_name' => (string) $otherClass->name,
                        'message' => sprintf(
                            'Conflito com "%s" em %s (%s).',
                            $otherClass->name,
                            self::WEEKDAY_LABELS[$slot['weekday']] ?? '',
                            self::formatSlotRange($slot['start_time'], $slot['end_time']),
                        ),
                    ];
                }
            }
        }

        return $warnings;
    }

    /**
     * @param  array{weekday: int, start_time: string, end_time: ?string}  $a
     * @param  array{weekday: int, start_time: string, end_time: ?string}  $b
     */
    public static function slotsOverlap(array $a, array $b): bool
    {
        if ($a['weekday'] !== $b['weekday']) {
            return false;
        }

        [$aStart, $aEnd] = self::minuteRange($a['start_time'], $a['end_time']);
        [$bStart, $bEnd] = self::minuteRange($b['start_time'], $b['end_time']);

        return $aStart < $bEnd && $bStart < $aEnd;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private static function minuteRange(string $startTime, ?string $endTime): array
    {
        $start = self::toMinutes($startTime);
        $end = $endTime ? self::toMinutes($endTime) : ($start + 60);

        if ($end <= $start) {
            $end = $start + 60;
        }

        return [$start, $end];
    }

    private static function toMinutes(string $time): int
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return 0;
        }

        return ((int) $matches[1] * 60) + (int) $matches[2];
    }

    private static function normalizeTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        return null;
    }

    public static function formatDisplayTime(string $time): string
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return $time;
        }

        $hour = (int) $matches[1];
        $minute = $matches[2];

        if ($minute === '00') {
            return "{$hour}h";
        }

        return sprintf('%dh %s', $hour, $minute);
    }

    /**
     * @param  array<int, array{weekday: int, start_time: string, end_time: ?string}>|null  $slots
     */
    public static function summarizeSlots(?array $slots): string
    {
        $normalized = self::normalizeSlots($slots);

        if ($normalized === []) {
            return '—';
        }

        return collect($normalized)
            ->map(function (array $slot) {
                $day = self::WEEKDAY_SHORT[$slot['weekday']] ?? '?';

                return $day.' '.self::formatSlotRange($slot['start_time'], $slot['end_time']);
            })
            ->join(' · ');
    }
}
