<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Authentication;

use Livewire\Component;

class Login extends Component
{
    public function render()
    {
        return view('adminhub::livewire.pages.authentication.login')
            ->layout('adminhub::layouts.base');
    }
}
