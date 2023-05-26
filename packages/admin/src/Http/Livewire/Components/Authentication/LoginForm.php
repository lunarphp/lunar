<?php

namespace Lunar\Hub\Http\Livewire\Components\Authentication;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\ComponentConcerns\PerformsRedirects;

class LoginForm extends Component
{
    use AuthorizesRequests;
    use PerformsRedirects;

    /**
     * The staff members email address.
     *
     * @var string
     */
    public $email;

    /**
     * The staff users password.
     *
     * @var string
     */
    public $password;

    /**
     * Whether to set the remember token.
     *
     * @var bool
     */
    public $remember = false;

    /**
     * {@inheritDoc}
     */
    protected $rules = [
        'email' => 'required|email',
        'remember' => 'nullable',
        'password' => 'required',
    ];

    /**
     * Perform the login.
     *
     * @return \Symfony\Component\HttpFoundation\Response|void
     */
    public function login()
    {
        $this->validate();

        $authCheck = Auth::guard('staff')->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember);

        if ($authCheck) {
            return redirect()->intended(route('hub.index'));
        }

        session()->flash('error', 'The provided credentials do not match our records.');
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('adminhub::livewire.components.login-form')
            ->layout('adminhub::layouts.base');
    }
}
