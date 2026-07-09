<?php

namespace Lunar\Panel\Slots;

class Slot
{
    /**
     * @param  string  $zone  "{page}:{region}[:position]", e.g. "customers.show:main:after"
     * @param  string  $component  Namespaced JS component name, e.g. "my-addon::SeoSection"
     * @param  array<string, mixed>  $props
     */
    public function __construct(
        public readonly string $zone,
        public readonly string $component,
        public readonly array $props = [],
        public readonly ?string $permission = null,
        public readonly int $priority = 50,
    ) {}

    /** @return array{component: string, props: array<string, mixed>, priority: int} */
    public function toArray(): array
    {
        return [
            'component' => $this->component,
            'props' => $this->props,
            'priority' => $this->priority,
        ];
    }
}
