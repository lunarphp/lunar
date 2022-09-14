<?php

namespace Lunar\Hub\Http\Livewire\Pages\Authentication;

use Livewire\Component;

class PasswordReset extends Component
{
    public function render()
    {
        return view('adminhub::livewire.pages.authentication.password-reset')
            ->layout('adminhub::layouts.base');
    }
}
