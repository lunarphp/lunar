<?php

namespace Lunar\Panel\Navigation;

use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;

class NavigationGroup
{
    /** @param NavigationItem[] $items */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly int $priority = 50,
        public array $items = [],
        public readonly ?string $section = null,
        public readonly ?Position $position = null,
    ) {}

    public function addItem(NavigationItem $item): void
    {
        $this->items[] = $item;
    }

    /** `priority` remains the ergonomic shortcut; an explicit Position wins when given. */
    public function position(): Position
    {
        return $this->position ?? Position::priority($this->priority);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $sortedItems = collect((new OrderResolver)->sort(
            $this->items,
            fn (NavigationItem $item) => $item->key,
            fn (NavigationItem $item) => $item->position(),
        ))
            ->map(fn (NavigationItem $item) => $item->toArray())
            ->all();

        return [
            'key' => $this->key,
            'label' => __($this->label),
            'priority' => $this->priority,
            'section' => $this->section,
            'items' => $sortedItems,
        ];
    }
}
