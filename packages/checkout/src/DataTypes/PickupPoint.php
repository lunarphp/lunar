<?php

namespace Lunar\Checkout\DataTypes;

/**
 * A place a customer can collect an order from, as the host describes it
 * (spec 0013 §A). `lines` are display lines, not a structured address: the
 * package renders them and never interprets them. `location` is optional;
 * with it, and a customer origin, the checkout shows the distance.
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
        public ?Coordinates $location = null,
    ) {}

    /**
     * @return array{handle: string, name: string, lines: list<string>, meta: array<string, mixed>, location: array{latitude: float, longitude: float}|null}
     */
    public function toArray(): array
    {
        return [
            'handle' => $this->handle,
            'name' => $this->name,
            'lines' => array_values($this->lines),
            'meta' => $this->meta,
            'location' => $this->location?->toArray(),
        ];
    }

    /**
     * @param  array{handle: string, name: string, lines?: list<string>, meta?: array<string, mixed>, location?: array{latitude: float|int|string, longitude: float|int|string}|null}  $data
     */
    public static function fromArray(array $data): self
    {
        $location = $data['location'] ?? null;

        return new self(
            handle: (string) $data['handle'],
            name: (string) $data['name'],
            lines: array_values($data['lines'] ?? []),
            meta: $data['meta'] ?? [],
            location: $location === null
                ? null
                : new Coordinates((float) $location['latitude'], (float) $location['longitude']),
        );
    }
}
