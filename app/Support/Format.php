<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Formattazione italiana condivisa da UI ed export. */
final class Format
{
    public static function displayTimezone(): string
    {
        return config('app.display_timezone', 'Europe/Rome');
    }

    public static function dateTime(?CarbonInterface $value, string $format = 'd/m/Y H:i'): string
    {
        return $value?->copy()->setTimezone(self::displayTimezone())->format($format) ?? '—';
    }

    public static function date(?CarbonInterface $value, string $format = 'd/m/Y'): string
    {
        return $value?->copy()->setTimezone(self::displayTimezone())->format($format) ?? '—';
    }

    public static function decimal(float|string|null $value, int $decimals = 2): string
    {
        if ($value === null) {
            return '—';
        }

        return number_format((float) $value, $decimals, ',', '.');
    }

    public static function money(float|string|null $value, int $decimals = 2): string
    {
        return $value === null ? '—' : '€ '.self::decimal($value, $decimals);
    }

    public static function percent(float|string|null $value, int $decimals = 1): string
    {
        return $value === null ? '—' : self::decimal($value, $decimals).'%';
    }

    public static function kg(float|string|null $value, int $decimals = 2): string
    {
        return $value === null ? '—' : self::decimal($value, $decimals).' kg';
    }

    /** Countdown leggibile: "2g 3h", "4h 12m", "8 min". */
    public static function countdown(?CarbonInterface $deadline, ?CarbonInterface $now = null): string
    {
        if (! $deadline) {
            return '—';
        }

        $now ??= now();

        if ($deadline->lessThanOrEqualTo($now)) {
            return 'Scaduta';
        }

        $minutes = (int) ceil($now->diffInMinutes($deadline, true));
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $mins = $minutes % 60;

        if ($days > 0) {
            return "{$days}g {$hours}h";
        }

        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }

        return "{$mins} min";
    }
}
