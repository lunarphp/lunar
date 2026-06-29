<?php

namespace Lunar\Core\Shipping\Carriers;

use Lunar\Core\Contracts\ShippingCarrier;

/**
 * Base class for shipping carriers. Implements the shared tracking mechanics —
 * URL-template substitution and optional tracking-number validation — so a
 * concrete carrier only declares its key, name, services and (optionally) a
 * tracking URL template and number pattern. Register carriers as classes
 * against the {@see CarrierManifest} from a service
 * provider (container-for-behaviour); core registers the batteries-included
 * carriers the same way.
 */
abstract class Carrier implements ShippingCarrier
{
    /**
     * The unique key identifying this carrier (e.g. "royal-mail").
     */
    abstract public function getKey(): string;

    /**
     * The human readable carrier name (e.g. "Royal Mail").
     */
    abstract public function getName(): string;

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [];
    }

    public function getTrackingUrl(string $trackingNumber): ?string
    {
        $template = $this->trackingUrlTemplate();

        if (! $template) {
            return null;
        }

        return str_replace('{tracking_number}', rawurlencode($trackingNumber), $template);
    }

    public function validateTrackingNumber(string $trackingNumber): bool
    {
        $pattern = $this->trackingNumberPattern();

        if (! $pattern) {
            return true;
        }

        return (bool) preg_match($pattern, $trackingNumber);
    }

    /**
     * The tracking URL template, with the "{tracking_number}" placeholder
     * substituted in, or null when this carrier cannot resolve tracking URLs.
     */
    protected function trackingUrlTemplate(): ?string
    {
        return null;
    }

    /**
     * The regular expression a valid tracking number must match, or null to
     * accept any reference.
     */
    protected function trackingNumberPattern(): ?string
    {
        return null;
    }
}
