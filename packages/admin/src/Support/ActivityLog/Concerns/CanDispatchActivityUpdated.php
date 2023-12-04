<?php

namespace Lunar\Admin\Support\ActivityLog\Concerns;

use Lunar\Admin\Livewire\Components\ActivityLogFeed;

trait CanDispatchActivityUpdated
{
    private function dispatchActivityUpdated(): bool
    {
        $this->dispatch(ActivityLogFeed::UPDATED)->to(ActivityLogFeed::class);

        return true;
    }
}
