<?php

namespace Lunar\Filament\Support;

use Filament\Support\Colors\Color;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\OrderState;

class OrderStatus
{
    protected static array $cachedStatusColor = [];

    protected static array $cachedStatusLabel = [];

    public static function getLabel(?string $status): string
    {
        if (! filled($status)) {
            return 'N/A';
        }

        return static::$cachedStatusLabel[$status] ??= static::resolveLabel($status);
    }

    public static function getColor(?string $status): array
    {
        return static::$cachedStatusColor[$status] ??= Color::generateV3Palette(
            (string) config('lunar-filament.order.status_colors.'.$status, '#7C7C7C')
        );
    }

    protected static function resolveLabel(string $status): string
    {
        /** @var class-string<OrderState>|null $class */
        $class = OrderState::resolveStateClass($status);

        if ($class && class_exists($class)) {
            return (new $class(new Order))->label();
        }

        return $status;
    }
}
