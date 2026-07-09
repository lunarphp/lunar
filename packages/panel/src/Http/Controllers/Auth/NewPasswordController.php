<?php

namespace Lunar\Panel\Http\Controllers\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController
{
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ResetPassword', [
            'email' => $request->query('email'),
            'token' => $request->route('token'),
            'urls' => [
                'store' => route('panel.password.store'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::broker(config('lunar.staff.provider', 'staff'))->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (CanResetPassword $user) use ($request): void {
                $user->forceFill([
                    'password' => $request->string('password')->value(),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __('panel::auth.reset_invalid_token'),
            ]);
        }

        return redirect()->route('panel.login')->with('success', __('panel::auth.password_reset'));
    }
}
