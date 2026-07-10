<?php

namespace Lunar\Panel\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\Auth\EmailTwoFactor;
use Lunar\Panel\Http\Requests\Auth\LoginRequest;
use Lunar\Panel\PanelManager;

class AuthenticatedSessionController
{
    public function create(): Response
    {
        return Inertia::render('auth/Login', [
            'urls' => [
                'store' => route('panel.login.store'),
                'forgotPassword' => route('panel.password.request'),
            ],
        ]);
    }

    public function store(
        LoginRequest $request,
        PanelManager $manager,
        AppAuthentication $appAuthentication,
        EmailTwoFactor $emailTwoFactor,
    ): RedirectResponse {
        $request->ensureIsNotRateLimited();

        $guard = Auth::guard($manager->guard());
        $provider = $guard->getProvider();

        $user = $provider->retrieveByCredentials($request->only('email', 'password'));

        if (! $user || ! $provider->validateCredentials($user, ['password' => $request->string('password')->value()])) {
            $request->hitRateLimiter();

            throw ValidationException::withMessages([
                'email' => __('panel::auth.failed'),
            ]);
        }

        $request->clearRateLimiter();

        $request->session()->put([
            'panel.login.id' => $user->getAuthIdentifier(),
            'panel.login.remember' => $request->boolean('remember'),
        ]);

        // Every login is a two-step challenge — TOTP if the staff member has
        // it configured, otherwise an emailed one-time code, so a second
        // factor is always required one way or another.
        if (! $appAuthentication->isEnabled($user)) {
            $emailTwoFactor->send($user);
        }

        return redirect()->route('panel.two-factor.challenge');
    }

    public function destroy(Request $request, PanelManager $manager): RedirectResponse
    {
        Auth::guard($manager->guard())->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('panel.login');
    }
}
