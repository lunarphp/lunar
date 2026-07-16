<?php

namespace Lunar\Panel\Navigation;

class NavigationGroup
{
    /** @param NavigationItem[] $items */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly int $priority = 50,
        public array $items = [],
        public readonly ?string $section = null,
    ) {}

    public function addItem(NavigationItem $item): void
    {
        $this->items[] = $item;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $sortedItems = collect($this->items)
            ->sortBy('priority')
            ->map(fn (NavigationItem $item) => $item->toArray())
            ->values()
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
