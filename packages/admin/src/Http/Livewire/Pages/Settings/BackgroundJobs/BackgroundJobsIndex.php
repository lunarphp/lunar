<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\BackgroundJobs;

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
        return view('adminhub::livewire.pages.settings.background-jobs.index')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}
