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

    public function format(?string $formatString = null): string
    {
        $measurement = $this->toMeasurement ?? $this->fromMeasurement;

        if (! $formatString && $measurement) {
            [$type, $unit] = explode('.', $measurement, 2);
            $formatString = $this->measurements[$type][$unit]['format'] ?? null;
        }

        if (! $formatString) {
            return (string) $this->value;
        }

        $value = $this->value;
        $negative = $value < 0;

        if ($negative) {
            $value *= -1;
        }

        // Match decimal and thousand separators from the format string.
        // The format uses: comma for thousands, dot for decimal, exclamation for no-separator.
        preg_match_all('/[,.!]/', $formatString, $separators);

        $thousand = $separators[0][0] ?? null;

        if ($thousand === '!') {
            $thousand = '';
        }

        $decimal = $separators[0][1] ?? null;

        // Extract the numeric portion to determine decimal places.
        preg_match('/([0-9].*|)[0-9]/', $formatString, $valFormat);

        $valFormat = $valFormat[0] ?? '0';
        $decimals = $decimal ? strlen(substr(strrchr($valFormat, $decimal), 1)) : 0;

        $formatted = number_format($value, $decimals, $decimal, $thousand);

        if ($negative) {
            $formatted = '-'.$formatted;
        }

        return preg_replace('/([0-9].*|)[0-9]/', $formatted, $formatString);
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
