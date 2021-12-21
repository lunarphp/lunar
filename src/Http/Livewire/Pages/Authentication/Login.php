<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Authentication;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public function render()
    {
        return view('adminhub::livewire.pages.authentication.login')
            ->layout('adminhub::layouts.base');
    }
}
