<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\BackgroundJobs;

use Livewire\Component;

class BackgroundJobsIndex extends Component
{

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.background-jobs.index')
            ->layout('adminhub::layouts.base');
    }
}
