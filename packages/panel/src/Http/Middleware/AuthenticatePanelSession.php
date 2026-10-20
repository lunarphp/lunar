<?php

namespace Lunar\Panel\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

/**
 * Ends a panel session whose staff password has changed since it signed in.
 *
 * Runs after the panel's Authenticate, which makes the panel guard the
 * request's default, so the stock middleware records and checks the staff
 * password hash (`password_hash_staff`) rather than the storefront's. The only
 * change is where a logged-out request goes: the panel login, not the host
 * app's `login` route.
 */
class AuthenticatePanelSession extends AuthenticateSession
{
    protected function redirectTo(Request $request)
    {
        return $request->expectsJson() ? null : route('panel.login');
    }
}
