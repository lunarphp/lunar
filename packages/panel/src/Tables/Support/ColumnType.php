<?php

namespace Lunar\Panel\Tables\Support;

final class ColumnType
{
    public function __construct(
        public readonly string $name,
        public readonly array $options = [],
    ) {}

    public static function currency(?string $code = null): static
    {
        return new self('currency', array_filter(['code' => $code]));
    }

    public static function badge(): static
    {
        return new static('badge');
    }

    public static function date(?string $format = null): static
    {
        return new static('date', array_filter(['format' => $format]));
    }

    public static function boolean(): static
    {
        return new static('boolean');
    }

    public static function image(): static
    {
        return new static('image');
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'options' => $this->options,
        ];
    }
}
