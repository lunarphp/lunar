<?php

namespace Lunar\Admin\Support\ActivityLog;

use Illuminate\Support\Collection;
use Lunar\Admin\Support\ActivityLog\Orders\Address;
use Lunar\Admin\Support\ActivityLog\Orders\Capture;
use Lunar\Admin\Support\ActivityLog\Orders\EmailNotification;
use Lunar\Admin\Support\ActivityLog\Orders\FulfilmentUpdate;
use Lunar\Admin\Support\ActivityLog\Orders\Intent;
use Lunar\Admin\Support\ActivityLog\Orders\OrderCancelled;
use Lunar\Admin\Support\ActivityLog\Orders\OrderClosed;
use Lunar\Admin\Support\ActivityLog\Orders\OrderReopened;
use Lunar\Admin\Support\ActivityLog\Orders\Refund;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

class Manifest
{
    public array $events = [];

    public function __construct()
    {
        $this->events = [
            Order::morphName() => [
                Comment::class,
                Capture::class,
                Intent::class,
                Refund::class,
                EmailNotification::class,
                Address::class,
                TagsUpdate::class,
                FulfilmentUpdate::class,
                OrderClosed::class,
                OrderReopened::class,
                OrderCancelled::class,
            ],
            Product::morphName() => [
                Comment::class,
            ],
            ProductVariant::morphName() => [
                Comment::class,
            ],
        ];
    }

    /**
     * Add an activity log render.
     */
    public function addRender(string $subject, string $renderer): self
    {
        if (class_exists($subject) && new $subject instanceof Base) {
            $subject = $subject::morphName();
        }

        if (empty($this->events[$subject])) {
            $this->events[$subject] = [];
        }

        $this->events[$subject][] = $renderer;

        return $this;
    }

    /**
     * Return the items from a given subject.
     */
    public function getItems(string $subject): Collection
    {
        if (class_exists($subject) && new $subject instanceof Base) {
            $subject = $subject::morphName();
        }

        return collect($this->events[$subject] ?? [])
            ->merge([
                Update::class,
                Create::class,
            ])->map(function ($subject) {
                $class = new $subject;

                return [
                    'event' => $class->getEvent(),
                    'class' => $class,
                ];
            });
    }
}
