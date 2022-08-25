<?php

namespace GetCandy\Hub\Listeners;

use Illuminate\Routing\Events\RouteMatched;

class SetStaffAuthMiddlewareListener
{
    /**
     * Handle the event.
     *
     * @param  RouteMatched  $event
     * @return void
     */
    public function handle(RouteMatched $event)
    {
        // Is this a livewire route and are we looking for a hub component.
        $routeName = $event->route->getName();

        $isLivewire = str_contains($routeName, 'livewire');
        $isHubComponent = str_contains($routeName, 'hub');
        $isModal = str_contains($routeName, 'livewire-ui-modal');
        $isLoginComponent = str_contains($routeName, 'login');
        $isPasswordReset = str_contains($routeName, 'password-reset');

        if ($isLivewire && ($isHubComponent || $isModal) && ! $isLoginComponent & ! $isPasswordReset) {
            $event->route->middleware(
                array_merge($event->route->middleware(), ['auth:staff'])
            );
        }
    }
}
