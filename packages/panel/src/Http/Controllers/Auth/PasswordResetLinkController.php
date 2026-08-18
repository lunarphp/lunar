<?php

namespace Lunar\Panel\Http\Controllers\Auth;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Panel\Notifications\ResetPassword;

class PasswordResetLinkController
{
    public function create(): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'urls' => [
                'store' => route('panel.password.email'),
                'login' => route('panel.login'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::broker(config('lunar.staff.provider', 'staff'))->sendResetLink(
            $request->only('email'),
            fn (CanResetPassword $user, string $token) => $user->notify(new ResetPassword($token)),
        );

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => __('panel::auth.reset_throttled'),
            ]);
        }

        // Unknown emails get the same success response so the form
        // cannot be used to enumerate staff accounts.
        return back()->with('success', __('panel::auth.reset_link_sent'));
    }
}
