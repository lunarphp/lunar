<?php

namespace Lunar\Panel\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Lunar\Panel\PanelManager;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // A login waiting on its second factor resumes at the challenge rather
        // than starting over, so reopening the panel mid-login (another tab, a
        // reload) does not discard the pending login. The login screen itself
        // stays reachable for "Use a different account".
        return $request->hasSession() && $request->session()->has('panel.login.id')
            ? route('panel.two-factor.challenge')
            : route('panel.login');
    }

    /** @param  string[]  $guards */
    protected function authenticate($request, array $guards): void
    {
        $manager = app(PanelManager::class);

        parent::authenticate($request, [$manager->guard()]);

        // Serve the panel in the staff member's preferred locale for the whole
        // request — server-rendered labels (navigation, validation messages)
        // and the shared `locale` prop the frontend boots vue-i18n from.
        $preferredLocale = $manager->user()?->preferred_locale ?? null;

        if ($preferredLocale && in_array($preferredLocale, $manager->availableLocales(), true)) {
            app()->setLocale($preferredLocale);
        }
    }
}
