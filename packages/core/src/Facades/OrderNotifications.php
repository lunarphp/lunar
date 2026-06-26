<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;

/**
 * @method static OrderNotificationManifest register(string $key, class-string $notification, ?string $label = null, array<int, string> $on = [], bool $manual = true, NotificationScope $scope = NotificationScope::Order)
 * @method static OrderNotificationManifest forget(string ...$keys)
 * @method static class-string|null get(string $key)
 * @method static string|null label(?string $key)
 * @method static array<string, string> sendable(NotificationScope $scope = NotificationScope::Order)
 * @method static array<int, class-string> triggeredBy(string $status, NotificationScope $scope = NotificationScope::Order)
 *
 * @see OrderNotificationManifest
 */
class OrderNotifications extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return OrderNotificationManifest::class;
    }
}
