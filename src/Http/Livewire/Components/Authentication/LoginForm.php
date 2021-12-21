<?php

namespace GetCandy\Hub\Http\Livewire\Components\Authentication;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginForm extends Component
{
    use AuthorizesRequests;

    public $loggingIn = false;

    public $email;

    public $password;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        $this->loggingIn = true;

        $authCheck = Auth::guard('staff')->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ]);

        if ($authCheck) {
            return redirect()->route('hub.index');
        }

        $this->loggingIn = false;

        session()->flash('error', 'The provided credentials do not match our records.');
    }

    public function render()
    {
        return view('adminhub::livewire.components.login-form')
            ->layout('adminhub::layouts.base');
    }
}
