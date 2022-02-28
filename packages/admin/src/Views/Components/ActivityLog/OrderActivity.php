<?php

namespace GetCandy\Hub\Views\Components\ActivityLog;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OrderActivity extends Component
{
    public Activity $activity;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.activity-log.order-activity');
    }
}
