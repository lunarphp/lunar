<?php

namespace Lunar\SearchRelevance\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Loads the shopper's session so the events endpoint can read who they are
 * (their cart, their session id), and never saves it or sends its cookie.
 *
 * The click beacon is in flight alongside the navigation it starts, or a
 * basket add. Laravel's StartSession writes the whole session payload back at
 * the end of every request, so a beacon that read the session before the
 * other request saved it would write the old copy over it: a guest's first
 * basket add could lose the new cart id. The endpoint only reads the session,
 * so it has nothing to save.
 */
class ReadOnlySession
{
    public function __construct(protected SessionManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (is_null($this->manager->getSessionConfig()['driver'] ?? null)) {
            return $next($request);
        }

        $session = $this->manager->driver();

        $session->setId($request->cookies->get($session->getName()));
        $session->setRequestOnHandler($request);
        $session->start();

        $request->setLaravelSession($session);

        return $next($request);
    }
}
