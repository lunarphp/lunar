<?php

namespace Lunar\Panel\Navigation;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;

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
        public readonly ?Position $position = null,
    ) {}

    public function addChild(NavigationItem $child): void
    {
        $this->children[] = $child;
    }

    /** `priority` remains the ergonomic shortcut; an explicit Position wins when given. */
    public function position(): Position
    {
        return $this->position ?? Position::priority($this->priority);
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
        $visibleChildren = collect($this->children)
            ->filter(function (NavigationItem $child) use ($user): bool {
                if ($child->permission === null) {
                    return true;
                }

                return $user !== null && $user->can($child->permission);
            })
            ->values()
            ->all();

        $filteredChildren = collect((new OrderResolver)->sort(
            $visibleChildren,
            fn (NavigationItem $child) => $child->key,
            fn (NavigationItem $child) => $child->position(),
        ))
            ->map(fn (NavigationItem $child) => $child->toArray($user))
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
