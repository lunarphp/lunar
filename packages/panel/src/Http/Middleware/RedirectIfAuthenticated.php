<?php

namespace Lunar\Panel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lunar\Panel\PanelManager;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function __construct(protected PanelManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard($this->manager->guard())->check()) {
            return redirect()->route('panel.dashboard');
        }

        return $next($request);
    }
}
