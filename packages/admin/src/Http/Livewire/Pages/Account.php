<?php

namespace Lunar\Hub\Http\Livewire\Pages;

use Livewire\Component;
use Lunar\Hub\Models\Staff;

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
