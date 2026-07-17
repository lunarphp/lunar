<?php

namespace Lunar\Panel\Drafts;

final class CommitResult
{
    /**
     * @param  array<int, array{key: string, label: string, mine: mixed, base: mixed, theirs: mixed}>  $conflicts
     */
    private function __construct(
        public readonly bool $committed,
        public readonly array $conflicts,
    ) {}

    public static function committed(): self
    {
        return new self(true, []);
    }

    /**
     * @param  array<int, array{key: string, label: string, mine: mixed, base: mixed, theirs: mixed}>  $conflicts
     */
    public static function conflicted(array $conflicts): self
    {
        return new self(false, $conflicts);
    }
}
