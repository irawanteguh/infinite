<?php

namespace Modules\Organization\Services;

class IndicatorDeadline
{
    public static function overdueMonths($year, array $submittedMonths, $day, \DateTimeImmutable $now, $expiresAt = null)
    {
        $missing = [];
        for ($month = 1; $month <= 12; $month++) {
            if (in_array($month, array_map('intval', $submittedMonths), true)) continue;
            if (self::hasStarted($year, $month, $now) && !self::allows($year, $month, $day, $now, $expiresAt)) {
                $missing[] = sprintf('%02d', $month);
            }
        }
        return $missing;
    }

    public static function hasStarted($year, $month, \DateTimeImmutable $now)
    {
        if (!preg_match('/^\d{4}$/', (string) $year) || !checkdate((int) $month, 1, (int) $year)) {
            return false;
        }
        return $now >= new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month), $now->getTimezone());
    }

    public static function allows($year, $month, $day, \DateTimeImmutable $now, $expiresAt = null)
    {
        if (!self::hasStarted($year, $month, $now)) {
            return false;
        }
        $period = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month), $now->getTimezone());
        $nextMonth = $period->modify('+1 month');
        $cutoff = $nextMonth->setDate((int) $nextMonth->format('Y'), (int) $nextMonth->format('m'), min((int) $day, (int) $nextMonth->format('t')))->modify('+1 day');
        return $now < $cutoff || ($expiresAt !== null && $now < new \DateTimeImmutable($expiresAt, $now->getTimezone()));
    }
}
