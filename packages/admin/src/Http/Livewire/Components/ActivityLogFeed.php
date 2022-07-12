<?php

namespace GetCandy\Hub\Http\Livewire\Components;

use GetCandy\Hub\Facades\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogFeed extends Component
{
    use WithPagination;

    /**
     * The log subject to get activity for.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public Model $subject;

    /**
     * Returns the activity log for the order.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActivityLogProperty()
    {
        return $this->subject->activities()
            ->whereNotIn('event', ['updated'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d');
            })->map(function ($logs) {
                return [
                    'date' => $logs->first()->created_at->startOfDay(),
                    'items' => $logs->map(function ($log) {
                        return [
                            'log' => $log,
                            'renderers' => $this->renderers->filter(function ($render) use ($log) {
                                return $render['event'] == $log->event;
                            })->pluck('class'),
                        ];
                    }),
                ];
            });
    }

    public function getRenderersProperty()
    {
        return ActivityLog::getItems(
            get_class($this->subject)
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.activity-log-feed')
        ->layout('adminhub::layouts.base');
    }
}
