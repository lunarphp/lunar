<?php

namespace Lunar\Api\Storefront\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Lunar\Api\Contracts\CustomerResolver;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Contracts\LunarUser;
use Lunar\Core\Contracts\StorefrontSession;

/**
 * Authenticates against the host guard named by `lunar.api.storefront.guard`,
 * pushes the resolved customer into the storefront session and associates the
 * request's cart with the user.
 */
class ResolveCustomer extends Authenticate
{
    public function __construct(
        AuthFactory $auth,
        protected CustomerResolver $resolver,
        protected StorefrontSession $storefront,
        protected CartSession $cartSession,
    ) {
        parent::__construct($auth);
    }

    public function handle($request, Closure $next, ...$guards)
    {
        $guard = (string) config('lunar.api.storefront.guard');

        $this->authenticate($request, [$guard]);

        $user = $this->auth->guard($guard)->user();

        if ($user && ($customer = $this->resolver->resolve($user))) {
            $this->storefront->setCustomer($customer);
        }

        if ($user instanceof LunarUser && ($cart = $this->cartSession->current()) && ! $cart->user_id) {
            $cart->associate($user, (string) config('lunar.api.storefront.cart_association_policy', 'merge'));
        }

        return $next($request);
    }

    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}
