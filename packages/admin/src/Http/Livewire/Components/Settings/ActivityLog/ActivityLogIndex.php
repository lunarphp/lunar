<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\ActivityLog;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogIndex extends Component
{
    use WithPagination;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.activity-log.index', [
            'logs' => Activity::whereLogName('lunar')->with('causer')->orderBy('created_at', 'desc')->paginate(25),
        ])->layout('adminhub::layouts.base');
    }
}
