<?php

namespace Lunar\Panel\Navigation;

use Illuminate\Contracts\Auth\Authenticatable;

class NavigationItem
{
    /** @param NavigationItem[] $children */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly ?string $icon = null,
        public readonly ?string $route = null,
        public readonly ?string $permission = null,
        public readonly int $priority = 50,
        public array $children = [],
        public readonly ?string $badge = null,
        public readonly bool $exact = false,
    ) {}

    public function addChild(NavigationItem $child): void
    {
        $this->children[] = $child;
    }

    protected function resolveUrl(): ?string
    {
        if (! $this->route) {
            return null;
        }

        try {
            return route($this->route);
        } catch (\Exception) {
            return null;
        }
    }

    /** @return array<string, mixed> */
    public function toArray(?Authenticatable $user = null): array
    {
        $filteredChildren = collect($this->children)
            ->filter(function (NavigationItem $child) use ($user): bool {
                if ($child->permission === null) {
                    return true;
                }

                return $user !== null && $user->can($child->permission);
            })
            ->sortBy('priority')
            ->map(fn (NavigationItem $child) => $child->toArray($user))
            ->values()
            ->all();

        return [
            'key' => $this->key,
            'label' => __($this->label),
            'icon' => $this->icon,
            'url' => $this->resolveUrl(),
            'priority' => $this->priority,
            'badge' => $this->badge,
            'exact' => $this->exact,
            'children' => $filteredChildren,
        ];
    }
}
