<?php

namespace Lunar\Panel\Tables\Support;

final class Position
{
    public function __construct(
        public readonly string $type,
        public readonly string|int|null $reference = null,
    ) {}

    public static function first(): static
    {
        return new self('first');
    }

    public static function last(): static
    {
        return new static('last');
    }

    public static function after(string $key): static
    {
        return new static('after', $key);
    }

    public static function before(string $key): static
    {
        return new static('before', $key);
    }

    public static function priority(int $n): static
    {
        return new static('priority', $n);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'reference' => $this->reference,
        ];
    }
}
