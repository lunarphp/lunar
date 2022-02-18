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
        $isLivewire = str_contains($event->route->getName(), 'livewire');
        $isHubComponent = str_contains($event->route->name, 'hub');
        $isModal = str_contains($event->route->name, 'livewire-ui-modal');
        $isLoginComponent = str_contains($event->route->name, 'login');
        $isPasswordReset = str_contains($event->route->name, 'password-reset');

        if ($isLivewire && ($isHubComponent || $isModal) && ! $isLoginComponent & ! $isPasswordReset) {
            $event->route->middleware(
                array_merge($event->route->middleware(), ['auth:staff'])
            );
        }
    }
}
