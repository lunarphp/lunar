<?php

namespace Lunar\Panel\Navigation;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;

class NavigationRegistry
{
    /** @var array<string, NavigationGroup> */
    protected array $groups = [];

    /** @var NavigationItem[] */
    protected array $ungroupedItems = [];

    protected ?string $currentSection = null;

    public function beginSection(string $key): void
    {
        $this->currentSection = $key;
    }

    public function endSection(): void
    {
        $this->currentSection = null;
    }

    public function group(string $key, string $label, int $priority = 50, ?Position $position = null): static
    {
        if (! isset($this->groups[$key])) {
            $this->groups[$key] = new NavigationGroup($key, $label, $priority, section: $this->currentSection, position: $position);
        }

        return $this;
    }

    public function addItem(string $groupKey, NavigationItem $item): static
    {
        if (! isset($this->groups[$groupKey])) {
            $this->group($groupKey, $groupKey);
        }

        $this->groups[$groupKey]->addItem($item);

        return $this;
    }

    public function addTopLevelItem(NavigationItem $item): static
    {
        $this->ungroupedItems[] = $item;

        return $this;
    }

    public function addChildItem(string $parentItemKey, NavigationItem $child): static
    {
        foreach ($this->groups as $group) {
            foreach ($group->items as $item) {
                if ($item->key === $parentItemKey) {
                    $item->addChild($child);

                    return $this;
                }
            }
        }

        foreach ($this->ungroupedItems as $item) {
            if ($item->key === $parentItemKey) {
                $item->addChild($child);

                return $this;
            }
        }

        return $this;
    }

    public function firstItem(): ?NavigationItem
    {
        foreach ($this->orderGroups($this->groups) as $group) {
            $sorted = $this->orderItems($group->items);

            if ($sorted !== []) {
                return $sorted[0];
            }
        }

        return $this->orderItems($this->ungroupedItems)[0] ?? null;
    }

    /**
     * @param  iterable<NavigationGroup>  $groups
     * @return list<NavigationGroup>
     */
    protected function orderGroups(iterable $groups): array
    {
        return (new OrderResolver)->sort(
            $groups,
            fn (NavigationGroup $group) => $group->key,
            fn (NavigationGroup $group) => $group->position(),
        );
    }

    /**
     * @param  iterable<NavigationItem>  $items
     * @return list<NavigationItem>
     */
    protected function orderItems(iterable $items): array
    {
        return (new OrderResolver)->sort(
            $items,
            fn (NavigationItem $item) => $item->key,
            fn (NavigationItem $item) => $item->position(),
        );
    }

    /** @return array<int, mixed> */
    public function toArray(?Authenticatable $user = null, bool $skipMenus = false): array
    {
        $groups = collect($this->orderGroups($this->groups))
            ->map(function (NavigationGroup $group) use ($user): array {
                $visibleItems = collect($group->items)
                    ->filter(fn (NavigationItem $item) => $this->userCanSeeItem($user, $item))
                    ->all();

                $filteredItems = collect($this->orderItems($visibleItems))
                    ->map(fn (NavigationItem $item) => $item->toArray($user))
                    ->all();

                $groupArray = $group->toArray();
                $groupArray['items'] = $filteredItems;

                return $groupArray;
            })
            ->filter(fn (array $group) => ! empty($group['items']))
            ->values();

        $visibleTopLevel = collect($this->ungroupedItems)
            ->filter(fn (NavigationItem $item) => $this->userCanSeeItem($user, $item))
            ->all();

        $topLevel = collect($this->orderItems($visibleTopLevel))
            ->map(fn (NavigationItem $item) => $item->toArray($user))
            ->all();

        $menusConfig = config('lunar.panel.menus', []);

        if ($skipMenus || empty($menusConfig)) {
            return [
                'groups' => $groups->all(),
                'items' => $topLevel,
            ];
        }

        $menus = collect($menusConfig)
            ->map(function (array $menuConfig) use ($groups): array {
                $menu = new Menu(
                    key: $menuConfig['key'],
                    label: $menuConfig['label'],
                    icon: $menuConfig['icon'],
                    sections: $menuConfig['sections'] ?? [],
                );

                $menuGroups = $groups
                    ->filter(fn (array $group) => in_array($group['section'] ?? null, $menu->sections))
                    ->values()
                    ->all();

                return array_merge($menu->toArray(), [
                    'groups' => $menuGroups,
                    'items' => [],
                ]);
            })
            ->values()
            ->all();

        // Put top-level items in the first menu
        if (! empty($menus) && ! empty($topLevel)) {
            $menus[0]['items'] = $topLevel;
        }

        // Groups not matching any menu section go into the last menu
        $assignedSections = collect($menusConfig)->flatMap(fn (array $m) => $m['sections'] ?? [])->all();

        $ungrouped = $groups
            ->filter(fn (array $group) => ! in_array($group['section'] ?? null, $assignedSections))
            ->values()
            ->all();

        if (! empty($ungrouped) && ! empty($menus)) {
            $lastIndex = count($menus) - 1;
            $menus[$lastIndex]['groups'] = array_merge($menus[$lastIndex]['groups'], $ungrouped);
        }

        return ['menus' => $menus];
    }

    protected function userCanSeeItem(?Authenticatable $user, NavigationItem $item): bool
    {
        if ($item->permission === null) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $user->can($item->permission);
    }
}
