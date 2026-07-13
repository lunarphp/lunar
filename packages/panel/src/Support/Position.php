<?php

namespace Lunar\Panel\Support;

/**
 * Declarative placement for an ordered panel entry — navigation items, table
 * columns, and actions. Resolved by {@see OrderResolver}: numeric priority is
 * the coarse default, `before`/`after` anchor an entry relative to another
 * entry's key so add-ons can place work without guessing priority numbers.
 */
final class Position
{
    public function __construct(
        public readonly string $type,
        public readonly string|int|null $reference = null,
        public readonly int $priority = 50,
    ) {}

    public static function first(): static
    {
        return new self('first');
    }

    public static function last(): static
    {
        return new static('last');
    }

    public static function after(string $key, int $priority = 50): static
    {
        return new static('after', $key, $priority);
    }

    public static function before(string $key, int $priority = 50): static
    {
        return new static('before', $key, $priority);
    }

    public static function priority(int $n): static
    {
        return new static('priority', $n, $n);
    }

    /**
     * The comparable weight used to order non-anchored entries and to place an
     * anchored entry that could not be resolved against its target.
     */
    public function weight(): int
    {
        return match ($this->type) {
            'first' => PHP_INT_MIN,
            'last' => PHP_INT_MAX,
            default => $this->priority,
        };
    }

    public function isAnchored(): bool
    {
        return $this->type === 'before' || $this->type === 'after';
    }

    /** @return array{type: string, reference: string|int|null} */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'reference' => $this->reference,
        ];
    }
}
