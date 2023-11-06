<?php

namespace Lunar\Base\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;

trait LogsActivity
{
    use SpatieLogsActivity;

    public static array $logExcept = [];

    /**
     * Get the log options for the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('lunar')
            ->logAll()
            ->logExcept(array_merge(['updated_at'], static::getActivitylogExcept()))
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function addActivitylogExcept(array | string $fields)
    {
        $fields = Arr::wrap($fields);

        static::$logExcept = array_merge(static::$logExcept, $fields);
    }

    public static function getActivitylogExcept(): array
    {
        return array_merge(static::$defaultLogExcept ?? [], static::$logExcept); /** @phpstan-ignore-line */
    }
}
