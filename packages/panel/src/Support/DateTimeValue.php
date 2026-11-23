<?php

namespace Lunar\Panel\Support;

use Illuminate\Support\Carbon;

/**
 * Reads a date-time the panel receives and writes it back in the app
 * timezone. The browser sends instants (ISO 8601 with an offset, from a
 * `datetime-local` input read as the staff member's local time); a value
 * without an offset is read in the app timezone, the zone stored columns are
 * written in. A value that does not parse passes through untouched so a
 * `date` rule can reject it with a message rather than a parse exception.
 */
class DateTimeValue
{
    /**
     * The instant as ISO 8601 with the app timezone's offset: the canonical
     * draft value, which the browser can read back as the same instant.
     */
    public static function normalize(mixed $value): ?string
    {
        return static::format($value, fn (Carbon $date) => $date->toIso8601String());
    }

    /**
     * The instant as an app-timezone `Y-m-d H:i:s` string, the shape a
     * datetime column (or a pivot written without casts) stores.
     */
    public static function toStorage(mixed $value): ?string
    {
        return static::format($value, fn (Carbon $date) => $date->toDateTimeString());
    }

    /**
     * @param  callable(Carbon): string  $formatter
     */
    protected static function format(mixed $value, callable $formatter): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return $formatter(Carbon::parse($value)->setTimezone(config('app.timezone')));
        } catch (\Throwable) {
            return is_string($value) ? $value : null;
        }
    }
}
