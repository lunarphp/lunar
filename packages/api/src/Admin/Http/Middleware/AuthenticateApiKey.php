<?php

namespace Lunar\Api\Admin\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Lunar\Api\Models\ApiKey;
use Spatie\Activitylog\CauserResolver;

/**
 * Authenticates the admin surface against `lunar.api.admin.guard` and
 * attributes activity to the key's owner when it has one, or to the key
 * itself so an integration is distinguishable from a human.
 */
class AuthenticateApiKey extends Authenticate
{
    public function __construct(
        AuthFactory $auth,
        protected CauserResolver $causer,
    ) {
        parent::__construct($auth);
    }

    public function handle($request, Closure $next, ...$guards)
    {
        $guard = (string) config('lunar.api.admin.guard', 'lunar-api');

        $this->authenticate($request, [$guard]);

        $principal = $this->auth->guard($guard)->user();

        if (! $principal instanceof ApiKey) {
            return $next($request);
        }

        $principal->markUsed();
        $this->causer->setCauser($principal->staff ?? $principal);

        try {
            return $next($request);
        } finally {
            // The resolver is a singleton; a long-lived worker must not carry this key's causer into the next request.
            $this->causer->setCauser(null);
        }
    }

    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}
