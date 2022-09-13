<?php

namespace Lunar\Hub\Http\Livewire\Pages;

use Lunar\Hub\Models\Staff;
use Livewire\Component;

class Account extends Component
{
    public Staff $staff;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.account')
            ->layout('adminhub::layouts.app', [
                'title' => __('adminhub::account.title'),
            ]);
    }
}
