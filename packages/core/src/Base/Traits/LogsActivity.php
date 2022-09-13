<?php

namespace Lunar\Base\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;

trait LogsActivity
{
    use SpatieLogsActivity;

    /**
     * Get the log options for the activity log.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('getcandy')
            ->logAll()
            ->dontSubmitEmptyLogs()
            ->logExcept(['updated_at']);
    }
}
