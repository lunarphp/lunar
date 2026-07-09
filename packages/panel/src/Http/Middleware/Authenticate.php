<?php

namespace Lunar\Panel\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Lunar\Panel\PanelManager;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('panel.login');
    }

    /** @param  string[]  $guards */
    protected function authenticate($request, array $guards): void
    {
        parent::authenticate($request, [app(PanelManager::class)->guard()]);
    }
}
