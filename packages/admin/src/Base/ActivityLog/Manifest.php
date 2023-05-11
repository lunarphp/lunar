<?php

namespace Lunar\Hub\Base\ActivityLog;

use Illuminate\Support\Collection;
use Lunar\Hub\Base\ActivityLog\Orders\Capture;
use Lunar\Hub\Base\ActivityLog\Orders\EmailNotification;
use Lunar\Hub\Base\ActivityLog\Orders\Intent;
use Lunar\Hub\Base\ActivityLog\Orders\StatusUpdate;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class Manifest
{
    /**
     * The events to watch and render.
     *
     * @var array
     */
    public $events = [
        Order::class => [
            Comment::class,
            StatusUpdate::class,
            Capture::class,
            Intent::class,
            EmailNotification::class,
        ],
        Product::class => [
            Comment::class,
            Update::class,
            Create::class,
        ],
        ProductVariant::class => [
            Comment::class,
            Update::class,
            Create::class,
        ],
    ];

    /**
     * Add an activity log render.
     *
     * @return self
     */
    public function addRender(string $subject, string $renderer)
    {
        if (empty($this->events[$subject])) {
            $this->events[$subject] = [];
        }

        $this->events[$subject][] = $renderer;

        return $this;
    }

    /**
     * Return the items from a given subject.
     *
     * @param  string  $classname
     * @return Collection
     */
    public function getItems($subject)
    {
        return collect($this->events[$subject] ?? [])->map(function ($subject) {
            $class = new $subject;

            return [
                'event' => $class->getEvent(),
                'class' => $class,
            ];
        });
    }
}
