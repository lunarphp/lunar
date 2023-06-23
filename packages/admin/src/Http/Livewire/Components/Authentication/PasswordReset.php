<?php

namespace Lunar\Hub\Http\Livewire\Components\Authentication;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\ComponentConcerns\PerformsRedirects;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Mail\ResetPasswordEmail;
use Lunar\Hub\Models\Staff;
use Throwable;

class PasswordReset extends Component
{
    use AuthorizesRequests;
    use PerformsRedirects;
    use Notifies;

    /**
     * The staff members email address.
     *
     * @var string
     */
    public $email;

    /**
     * The new password.
     *
     * @var string
     */
    public $password;

    /**
     * The confirmed password.
     *
     * @var string
     */
    public $password_confirmation;

    /**
     * The reset token.
     *
     * @var string|null
     */
    public $token;

    protected $queryString = ['email', 'token'];

    public $invalid = false;

    /**
     * {@inheritDoc}
     */
    protected $rules = [
        'email' => 'required|email',
        'password' => 'nullable|confirmed',
        'password_confirmation' => 'nullable',
    ];

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        if ($this->token && ! request()->hasValidSignature()) {
            $this->invalid = true;
        }
    }

    /**
     * Process the reset form.
     *
     * @return void
     */
    public function process()
    {
        if (! $this->token) {
            $this->sendResetEmail();

            return;
        }

        $this->updatePasswordAndLogin();
    }

    /**
     * Send the reset email.
     *
     * @return void
     */
    public function sendResetEmail()
    {
        $this->validate();

        if ($staff = Staff::whereEmail($this->email)->first()) {
            cache(
                ['hub.password.reset.'.$staff->id => $token = Str::random()],
                now()->addMinutes(30)
            );

            Mail::to($staff->email)->send(new ResetPasswordEmail(
                encrypt($staff->id.'|'.$token)
            ));
        }

        $this->notify(
            __('adminhub::notifications.password-reset.email_sent')
        );
    }

    /**
     * Update the password and log staff in.
     *
     * @return void
     */
    public function updatePasswordAndLogin()
    {
        $this->validate([
            'password' => 'min:8|required|confirmed',
            'password_confirmation' => 'required',
        ]);

        try {
            $token = decrypt($this->token);

            [$staffId, $token] = explode('|', $token);

            $staff = Staff::findOrFail($staffId);
        } catch (Throwable $e) {
            $this->notify(
                __('adminhub::notifications.password-reset.invalid_token'),
                level: 'error'
            );

            return;
        }

        if (cache('hub.password.reset.'.$staffId) != $token) {
            $this->notify(
                __('adminhub::notifications.password-reset.invalid_token'),
                level: 'error'
            );

            return;
        }

        cache()->forget('password.reset.'.$staffId);

        $staff->password = Hash::make($this->password);
        $staff->save();

        Auth::guard('staff')->loginUsingId($staffId);

        $this->notify(
            __('adminhub::notifications.password-reset.password_updated')
        );

        $this->redirectRoute('hub.index');
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('adminhub::livewire.components.password-reset')
            ->layout('adminhub::layouts.base');
    }
}
