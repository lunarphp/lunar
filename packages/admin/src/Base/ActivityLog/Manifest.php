<?php

namespace GetCandy\Hub\Base\ActivityLog;

use GetCandy\Hub\Base\ActivityLog\Orders\Capture;
use GetCandy\Hub\Base\ActivityLog\Orders\EmailNotification;
use GetCandy\Hub\Base\ActivityLog\Orders\Intent;
use GetCandy\Hub\Base\ActivityLog\Orders\StatusUpdate;
use GetCandy\Models\Order;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use Illuminate\Support\Collection;

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
        ],
        ProductVariant::class => [
            Comment::class,
        ],
    ];

    /**
     * Add an activity log render.
     *
     * @param  string  $subject
     * @param  string  $renderer
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
