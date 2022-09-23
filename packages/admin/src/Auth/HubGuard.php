<?php

namespace Lunar\Hub\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Timebox;

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
        Request $request = null,
        Timebox $timebox = null
    ) {
        $this->name = 'lunarhub';
        $this->session = $session;
        $this->request = $request;
        $this->provider = $provider;
        $this->timebox = $timebox ?: new Timebox;

        $this->setCookieJar(app('cookie'));
    }
}
