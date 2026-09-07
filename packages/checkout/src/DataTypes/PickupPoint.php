<?php

namespace Lunar\Checkout\DataTypes;

/**
 * A place a customer can collect an order from, as the host describes it
 * (spec 0013 §A). `lines` are display lines, not a structured address: the
 * package renders them and never interprets them.
 */
final readonly class PickupPoint
{
    /**
     * @param  list<string>  $lines
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $handle,
        public string $name,
        public array $lines = [],
        public array $meta = [],
    ) {}

    /**
     * @return array{handle: string, name: string, lines: list<string>, meta: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'handle' => $this->handle,
            'name' => $this->name,
            'lines' => array_values($this->lines),
            'meta' => $this->meta,
        ];
    }

    /**
     * @param  array{handle: string, name: string, lines?: list<string>, meta?: array<string, mixed>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            handle: (string) $data['handle'],
            name: (string) $data['name'],
            lines: array_values($data['lines'] ?? []),
            meta: $data['meta'] ?? [],
        );
    }
}
