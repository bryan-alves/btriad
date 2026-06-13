<?php

namespace App\Support;

/** @deprecated Use ClassScheduleSupport */
class SiteScheduleFromClasses
{
    public const WEEKDAY_LABELS = ClassScheduleSupport::WEEKDAY_LABELS;

    public static function build(iterable $classes): array
    {
        return ClassScheduleSupport::buildPublicSchedule($classes);
    }
}
