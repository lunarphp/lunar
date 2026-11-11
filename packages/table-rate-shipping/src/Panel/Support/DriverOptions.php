<?php

namespace Lunar\Shipping\Panel\Support;

use Illuminate\Contracts\Translation\Translator;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;
use Lunar\Shipping\Interfaces\ShippingRateInterface;

/**
 * Every registered shipping driver as a select option. A driver with a lang
 * key under shippingmethod.form.driver.options takes that label; any other
 * (a consumer-registered driver, say) falls back to its own name().
 */
class DriverOptions
{
    public function __construct(
        protected ShippingMethodManagerInterface $shipping,
        protected Translator $translator,
    ) {}

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public function all(): array
    {
        return $this->shipping->getSupportedDrivers()
            ->map(function (ShippingRateInterface $driver, string $key): array {
                $langKey = "shipping::shippingmethod.form.driver.options.{$key}";

                return [
                    'key' => $key,
                    'label' => $this->translator->has($langKey) ? $this->translator->get($langKey) : $driver->name(),
                ];
            })
            ->values()
            ->all();
    }
}
