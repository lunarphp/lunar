<?php

namespace Lunar\Panel\Tables\Support;

/**
 * Generic renderer descriptor for an add-on table column without a custom
 * component. Rendered client-side by DataTableCell.vue.
 */
final class ColumnType
{
    public function __construct(
        public readonly string $name,
        public readonly array $options = [],
    ) {}

    /**
     * Formats the cell value as a decimal amount (not minor units) in the
     * given ISO currency code via Intl.NumberFormat.
     */
    public static function currency(?string $code = null): static
    {
        return new self('currency', array_filter(['code' => $code]));
    }

    public static function badge(): static
    {
        return new static('badge');
    }

    /** @param  string|null  $format  An Intl dateStyle: short, medium, long or full. */
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
