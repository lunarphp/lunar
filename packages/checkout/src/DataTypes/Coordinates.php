<?php

namespace Lunar\Checkout\DataTypes;

/**
 * A WGS84 point (spec 0013 §A). Carried by a pickup point that knows where it
 * is and by the customer origin a host can supply, so the checkout can say
 * how far each branch is.
 */
final readonly class Coordinates
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    /**
     * @return array{latitude: float, longitude: float}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
