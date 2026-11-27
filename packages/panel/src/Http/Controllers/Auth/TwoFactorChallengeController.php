<?php

namespace Lunar\Panel\Http\Controllers\Auth;

use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Auth\AppAuthentication;
use Lunar\Panel\Auth\EmailTwoFactor;
use Lunar\Panel\PanelManager;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        Router $router,
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

        return redirect()->to($this->landingUrl($request, $user, $router));
    }

    /**
     * Where a completed sign-in lands: the page the staff member was sent
     * to sign in from, unless a `can:` gate on that page's route refuses
     * them. Following a refused URL would answer the challenge form with a
     * 403, which Inertia shows as a raw error modal over the challenge, so
     * such a URL falls back to the dashboard. Only abilities without
     * arguments are checked here; a gate on a route model is left to the
     * page itself.
     */
    protected function landingUrl(Request $request, Staff $user, Router $router): string
    {
        $dashboard = route('panel.dashboard');
        $intended = $request->session()->pull('url.intended');

        if (! is_string($intended) || $intended === '') {
            return $dashboard;
        }

        try {
            $route = $router->getRoutes()->match(Request::create($intended));
        } catch (HttpExceptionInterface) {
            return $intended;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware) || ! str_contains($middleware, ':')) {
                continue;
            }

            [$name, $parameters] = explode(':', $middleware, 2);

            if (! in_array($name, ['can', Authorize::class], true) || str_contains($parameters, ',')) {
                continue;
            }

            if ($user->cannot($parameters)) {
                return $dashboard;
            }
        }

        return $intended;
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
