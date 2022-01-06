<?php

namespace GetCandy\Hub\Http\Livewire\Components;

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
    public $subject;

    public function getActivityProperty()
    {
        return Activity::with(['causer'])
            ->forSubject($this->subject)
            ->latest()
            ->get()
            ->groupBy(['batch_uuid']);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.activity-log-feed', [
            'activity' => $this->activity,
        ])->layout('adminhub::layouts.base');
    }
}
