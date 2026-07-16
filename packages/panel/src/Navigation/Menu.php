<?php

namespace Lunar\Panel\Navigation;

class Menu
{
    /**
     * @param  string[]  $sections
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $icon,
        public readonly array $sections = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => __($this->label),
            'icon' => $this->icon,
        ];
    }
}
