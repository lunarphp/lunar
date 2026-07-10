<?php

namespace Lunar\Panel\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\Auth\EmailTwoFactor;
use Lunar\Panel\PanelManager;

class TwoFactorChallengeController
{
    public function create(
        Request $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
        EmailTwoFactor $emailTwoFactor,
    ): Response|RedirectResponse {
        if (! $request->session()->has('panel.login.id')) {
            return redirect()->route('panel.login');
        }

        $user = $this->pendingUser($request, $manager);

        if (! $user) {
            return redirect()->route('panel.login');
        }

        $usesEmail = ! $appAuthentication->isEnabled($user);

        return Inertia::render('auth/TwoFactorChallenge', [
            'method' => $usesEmail ? 'email' : 'authenticator',
            'obfuscatedEmail' => $usesEmail ? $this->obfuscateEmail($user->email) : null,
            'cooldownRemaining' => $usesEmail ? $emailTwoFactor->cooldownRemaining($user) : 0,
            'urls' => [
                'store' => route('panel.two-factor.challenge.store'),
                'resend' => route('panel.two-factor.challenge.resend'),
                'login' => route('panel.login'),
            ],
        ]);
    }

    public function store(
        Request $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
        EmailTwoFactor $emailTwoFactor,
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

        $user = $this->pendingUser($request, $manager);

        if (! $user) {
            return redirect()->route('panel.login');
        }

        $valid = match (true) {
            ! $appAuthentication->isEnabled($user) => $emailTwoFactor->verifyAndConsume(
                $user,
                $request->string('code')->value(),
            ),
            $usingRecoveryCode => $appAuthentication->verifyAndConsumeRecoveryCode(
                $user,
                $request->string('recovery_code')->value(),
            ),
            default => $appAuthentication->verifyCode(
                $user->app_authentication_secret,
                $request->string('code')->value(),
                preventReuse: true,
            ),
        };

        if (! $valid) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                $errorField => __('panel::auth.invalid_code'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $guard = Auth::guard($manager->guard());
        $guard->login($user, (bool) $request->session()->pull('panel.login.remember', false));

        $request->session()->forget('panel.login.id');
        $request->session()->regenerate();

        return redirect()->intended(route('panel.dashboard'));
    }

    public function resend(
        Request $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
        EmailTwoFactor $emailTwoFactor,
    ): RedirectResponse {
        if (! $request->session()->has('panel.login.id')) {
            return redirect()->route('panel.login');
        }

        $user = $this->pendingUser($request, $manager);

        if (! $user) {
            return redirect()->route('panel.login');
        }

        // TOTP-enrolled staff have no use for an emailed code — nothing to
        // resend, so just bounce back to the challenge as-is.
        if ($appAuthentication->isEnabled($user)) {
            return redirect()->route('panel.two-factor.challenge');
        }

        if (! $emailTwoFactor->send($user)) {
            throw ValidationException::withMessages([
                'code' => __('panel::auth.throttle', [
                    'seconds' => $emailTwoFactor->cooldownRemaining($user),
                ]),
            ]);
        }

        return redirect()->route('panel.two-factor.challenge')
            ->with('success', __('panel::auth.code_resent'));
    }

    protected function pendingUser(Request $request, PanelManager $manager): ?Staff
    {
        $id = $request->session()->get('panel.login.id');

        if (! $id) {
            return null;
        }

        $user = Auth::guard($manager->guard())->getProvider()->retrieveById($id);

        if (! $user) {
            $request->session()->forget(['panel.login.id', 'panel.login.remember']);

            return null;
        }

        return $user;
    }

    protected function obfuscateEmail(string $email): string
    {
        $at = strpos($email, '@');

        if ($at === false || $at < 1) {
            return $email;
        }

        $local = substr($email, 0, $at);
        $domain = substr($email, $at);

        return $local[0].str_repeat('•', max(strlen($local) - 1, 3)).$domain;
    }
}
