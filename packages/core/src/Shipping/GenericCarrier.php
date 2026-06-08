<?php

namespace Lunar\Core\Shipping;

use Lunar\Core\Contracts\ShippingCarrier;

/**
 * A config-driven shipping carrier. Lets carriers be defined entirely in
 * config (key, name, tracking URL template, services and an optional tracking
 * number pattern) without writing a dedicated carrier class.
 */
class GenericCarrier implements ShippingCarrier
{
    /**
     * @param  array<int, string>  $services
     */
    public function __construct(
        protected string $key,
        protected string $name,
        protected ?string $trackingUrl = null,
        protected array $services = [],
        protected ?string $trackingNumberPattern = null,
    ) {}

    /**
     * Build a carrier from a config array shape.
     *
     * @param  array{name?: string, tracking_url?: ?string, services?: array<int, string>, tracking_number_pattern?: ?string}  $config
     */
    public static function fromConfig(string $key, array $config): self
    {
        return new self(
            key: $key,
            name: $config['name'] ?? $key,
            trackingUrl: $config['tracking_url'] ?? null,
            services: $config['services'] ?? [],
            trackingNumberPattern: $config['tracking_number_pattern'] ?? null,
        );
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return $this->services;
    }

    public function getTrackingUrl(string $trackingNumber): ?string
    {
        if (! $this->trackingUrl) {
            return null;
        }

        return str_replace('{tracking_number}', rawurlencode($trackingNumber), $this->trackingUrl);
    }

    public function validateTrackingNumber(string $trackingNumber): bool
    {
        if (! $this->trackingNumberPattern) {
            return true;
        }

        return (bool) preg_match($this->trackingNumberPattern, $trackingNumber);
    }
}
