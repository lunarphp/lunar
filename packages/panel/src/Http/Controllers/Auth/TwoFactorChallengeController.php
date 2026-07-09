<?php

namespace Lunar\Panel\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\PanelManager;

class TwoFactorChallengeController
{
    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('panel.login.id')) {
            return redirect()->route('panel.login');
        }

        return Inertia::render('auth/TwoFactorChallenge', [
            'urls' => [
                'store' => route('panel.two-factor.challenge.store'),
                'login' => route('panel.login'),
            ],
        ]);
    }

    public function store(
        Request $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
    ): RedirectResponse {
        $id = $request->session()->get('panel.login.id');

        if (! $id) {
            return redirect()->route('panel.login');
        }

        $request->validate([
            'code' => ['required_without:recovery_code', 'nullable', 'string'],
            'recovery_code' => ['required_without:code', 'nullable', 'string'],
        ]);

        $usingRecoveryCode = $request->filled('recovery_code');
        $errorField = $usingRecoveryCode ? 'recovery_code' : 'code';

        $throttleKey = 'lunar-panel:two-factor:'.$id;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                $errorField => __('panel::auth.throttle', [
                    'seconds' => RateLimiter::availableIn($throttleKey),
                ]),
            ]);
        }

        $guard = Auth::guard($manager->guard());
        $user = $guard->getProvider()->retrieveById($id);

        if (! $user) {
            $request->session()->forget(['panel.login.id', 'panel.login.remember']);

            return redirect()->route('panel.login');
        }

        $valid = $usingRecoveryCode
            ? $appAuthentication->verifyAndConsumeRecoveryCode($user, $request->string('recovery_code')->value())
            : $appAuthentication->verifyCode(
                $user->app_authentication_secret,
                $request->string('code')->value(),
                preventReuse: true,
            );

        if (! $valid) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                $errorField => __('panel::auth.invalid_code'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $guard->login($user, (bool) $request->session()->pull('panel.login.remember', false));

        $request->session()->forget('panel.login.id');
        $request->session()->regenerate();

        return redirect()->intended(route('panel.dashboard'));
    }
}
