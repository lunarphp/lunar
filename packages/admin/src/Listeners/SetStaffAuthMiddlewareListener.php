<?php

namespace GetCandy\Hub\Listeners;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Str;

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
        if ($this->isLivewireRoute($event->route->getName()) || $this->allowMiddleware($event->route->name)) {
            $event->route->middleware(
                array_merge($event->route->middleware(), ['auth:staff'])
            );
        }
    }

    /**
     * Matches livewire route which use dot separator.
     *
     * @param  string|null  $routeName
     * @return bool
     */
    protected function isLivewireRoute(?string $routeName): bool
    {
        return str_starts_with($routeName, 'livewire.');
    }

    /**
     * Matches routes which are allowed to use with auth:staff middleware.
     *
     * @param  string|null  $routeName
     * @return bool
     */
    protected function allowMiddleware(?string $routeName): bool
    {
        return Str::of($routeName)->contains(['hub', 'livewire-ui-modal']);
    }
}
