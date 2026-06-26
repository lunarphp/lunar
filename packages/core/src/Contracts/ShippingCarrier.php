<?php

namespace Lunar\Core\Contracts;

interface ShippingCarrier
{
    /**
     * The unique key identifying this carrier (e.g. "royal-mail").
     */
    public function getKey(): string;

    /**
     * The human readable carrier name (e.g. "Royal Mail").
     */
    public function getName(): string;

    /**
     * The shipping services this carrier offers, keyed by a stable service
     * identifier (the value stored against the tracking) with a translation
     * key or plain label as the value — used to populate the shipping method
     * options when recording tracking.
     *
     * @return array<string, string>
     */
    public function getServices(): array;

    /**
     * Build the public tracking URL for a given tracking number, or null
     * when this carrier cannot resolve one.
     */
    public function getTrackingUrl(string $trackingNumber): ?string;

    /**
     * Determine whether the given tracking number matches the format this
     * carrier expects. Carriers without a known format always pass.
     */
    public function validateTrackingNumber(string $trackingNumber): bool;
}
