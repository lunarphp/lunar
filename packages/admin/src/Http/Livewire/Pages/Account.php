<?php

namespace GetCandy\Hub\Http\Livewire\Pages;

use GetCandy\Hub\Models\Staff;
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
