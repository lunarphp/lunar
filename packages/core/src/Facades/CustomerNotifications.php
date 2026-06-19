<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\CustomerNotificationManifest;

/**
 * @method static CustomerNotificationManifest register(string $key, class-string $notification, ?string $label = null)
 * @method static array<string, string> all()
 * @method static class-string|null get(string $key)
 * @method static string|null label(?string $key)
 * @method static CustomerNotificationManifest forget(string ...$keys)
 * @method static bool isEmpty()
 *
 * @see CustomerNotificationManifest
 */
class CustomerNotifications extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return CustomerNotificationManifest::class;
    }
}
