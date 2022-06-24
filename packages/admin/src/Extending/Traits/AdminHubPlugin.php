<?php

namespace GetCandy\Hub\Extending\Traits;

use GetCandy\Hub\Base\ActivityLog\Orders\Capture;
use GetCandy\Hub\Base\ActivityLog\Comment;
use GetCandy\Hub\Base\ActivityLog\Orders\Intent;
use GetCandy\Hub\Base\ActivityLog\Orders\StatusUpdate;
use GetCandy\Hub\Facades\ActivityLog;
use GetCandy\Models\Order;

trait AdminHubPlugin
{
    protected $logRenderers = [
        Order::class => [
            Comment::class,
            StatusUpdate::class,
            Capture::class,
            Intent::class,
        ],
    ];

    public function register()
    {
        $this->booting(function () {
            ActivityLog::boot(
                $this->logRenderers
            );
        });
    }
}
