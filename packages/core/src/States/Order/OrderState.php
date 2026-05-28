<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Contracts\OrderStateConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class OrderState extends State
{
    abstract public function label(): string;

    public function isManualOverride(): bool
    {
        return false;
    }

    public static function config(): StateConfig
    {
        $config = app(OrderStateConfig::class);

        // No transitions are registered — the resolver writes order_status
        // directly via saveQuietly() / forceFill().
        return parent::config()
            ->default($config->defaultOrderState())
            ->registerState($config->orderStates());
    }
}
