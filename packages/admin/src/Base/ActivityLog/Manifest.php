<?php

namespace GetCandy\Hub\Base\ActivityLog;

use Spatie\Activitylog\Models\Activity;

class Manifest
{
    public $events = [];

    public function boot(array $events)
    {
        $this->events = $events;
    }

    public function getItems($classname)
    {
        return collect($this->events[$classname] ?? [])->map(function ($className) {
            $class = new $className;
            return [
                'event' => $class->getEvent(),
                'class' => $class,
            ];
        });
    }
}
