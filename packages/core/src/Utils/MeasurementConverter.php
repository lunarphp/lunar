<?php

namespace Lunar\Utils;

use InvalidArgumentException;

class MeasurementConverter
{
    protected array $measurements = [];

    protected ?string $fromMeasurement = null;

    protected ?string $toMeasurement = null;

    protected float $value = 0;

    public function __construct(array $measurements = [])
    {
        $this->measurements = $measurements;
    }

    public function setMeasurements(array $measurements): void
    {
        $this->measurements = $measurements;
    }

    public function getMeasurements(): array
    {
        return $this->measurements;
    }

    public function from(string $measurement): static
    {
        $instance = clone $this;
        $instance->fromMeasurement = $measurement;

        return $instance;
    }

    public function to(string $measurement): static
    {
        $this->toMeasurement = $measurement;

        return $this;
    }

    public function value(float $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function convert(): static
    {
        $fromFactor = $this->getFactor($this->fromMeasurement);
        $toFactor = $this->getFactor($this->toMeasurement);

        $this->value = ($this->value / $fromFactor) * $toFactor;

        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    protected function getFactor(string $measurement): float
    {
        [$type, $unit] = explode('.', $measurement, 2);

        if (! isset($this->measurements[$type][$unit]['unit'])) {
            throw new InvalidArgumentException("Unknown measurement: {$measurement}");
        }

        return (float) $this->measurements[$type][$unit]['unit'];
    }
}
