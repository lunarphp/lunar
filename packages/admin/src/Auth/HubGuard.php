<?php

namespace Lunar\Hub\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class HubGuard extends SessionGuard
{
    /**
     * Create a new authentication guard.
     *
     * @param  \Lunar\Hub\Auth\StaffProvider  $provider
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @param  \Symfony\Component\HttpFoundation\Request|null  $request
     * @return void
     */
    public function __construct(
        StaffProvider $provider,
        Session $session,
        Request $request = null
    ) {
        $this->name = 'getcandyhub';
        $this->session = $session;
        $this->request = $request;
        $this->provider = $provider;

        $this->setCookieJar(app('cookie'));
    }
}
