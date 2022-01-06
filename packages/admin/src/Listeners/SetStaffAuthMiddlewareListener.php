<?php

namespace GetCandy\Hub\Listeners;

use Illuminate\Routing\Events\RouteMatched;

class SetStaffAuthMiddlewareListener
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Routing\Events\RouteMatched $event
     *
     * @return void
     */
    public function handle(RouteMatched $event)
    {
        // Is this a livewire route and are we looking for a hub component.
        $isLivewire = strpos($event->route->getName(), 'livewire') !== false;
        $isHubComponent = strpos($event->route->name, 'hub') !== false;
        $isModal = strpos($event->route->name, 'livewire-ui-modal') !== false;
        $isLoginComponent = strpos($event->route->name, 'login') !== false;

        if ($isLivewire && ($isHubComponent || $isModal) && !$isLoginComponent) {
            $event->route->middleware(
                array_merge($event->route->middleware(), ['auth:staff'])
            );
        }
    }
}
