<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\ActivityLog;

use Livewire\Component;

class ActivityLogIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.activity-log.index')
            ->layout('adminhub::layouts.settings', [
                'title' => 'Activity Log',
            ]);
    }
}
