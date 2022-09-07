<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\ActivityLog;

use Livewire\Component;
use Livewire\WithPagination;

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
        return view('adminhub::livewire.components.settings.activity-log.index')
            ->layout('adminhub::layouts.base');
    }
}
