<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Authentication;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PasswordReset extends Component
{
    public function render()
    {
        return view('adminhub::livewire.pages.authentication.password-reset')
            ->layout('adminhub::layouts.base');
    }
}
