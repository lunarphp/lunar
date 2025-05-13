<?php

namespace Lunar\Admin\Support\ActivityLog;

use Illuminate\Support\Collection;
use Lunar\Admin\Support\ActivityLog\Orders\Address;
use Lunar\Admin\Support\ActivityLog\Orders\Capture;
use Lunar\Admin\Support\ActivityLog\Orders\EmailNotification;
use Lunar\Admin\Support\ActivityLog\Orders\Intent;
use Lunar\Admin\Support\ActivityLog\Orders\Refund;
use Lunar\Admin\Support\ActivityLog\Orders\StatusUpdate;
use Lunar\Base\BaseModel;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class Manifest
{
    public array $events = [];

    public function __construct()
    {
        $this->events = [
            Order::morphName() => [
                Comment::class,
                StatusUpdate::class,
                Capture::class,
                Intent::class,
                Refund::class,
                EmailNotification::class,
                Address::class,
                TagsUpdate::class,
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
        if (class_exists($subject) && new $subject instanceof BaseModel) {
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
        if (class_exists($subject) && new $subject instanceof BaseModel) {
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
