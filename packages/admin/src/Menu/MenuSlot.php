<?php

namespace Lunar\Hub\Menu;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MenuSlot
{
    /**
     * The sections which are in the slot.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $sections;

    /**
     * The groups which are in the slot.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $groups;

    /**
     * The items which are in the slot.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $items;

    /**
     * The menu slot handle.
     *
     * @var string
     */
    protected $handle;

    /**
     * The menu slot gate.
     *
     * @var string
     */
    protected ?string $gate = null;

    /**
     * Initialise the class.
     *
     * @param  string  $handle
     */
    public function __construct($handle)
    {
        $this->handle = $handle;
        $this->items = collect();
        $this->sections = collect();
        $this->groups = collect();
    }

    public function gate($gate)
    {
        $this->gate = $gate;

        return $this;
    }

    /**
     * Add an item to the menu slot.
     *
     * @param  string  $after
     * @return static
     */
    public function addItem(\Closure $callback, $after = null)
    {
        $item = tap(new MenuLink(), $callback);

        $index = false;

        if ($after) {
            $index = $this->items->search(function ($item) use ($after) {
                return $item->handle == $after;
            });
        }

        if (is_int($index)) {
            $this->items->splice($index + 1, 0, [$item]);

            return $this;
        }

        $this->items->push($item);

        return $this;
    }

    /**
     * Add multiple items.
     *
     * @return static
     */
    public function addItems(array $items)
    {
        foreach ($items as $item) {
            $this->items->push($item);
        }

        return $this;
    }

    /**
     * Get the items for the menu slot.
     */
    public function getItems(): Collection
    {
        return $this->items->filter(function ($item) {
            return ! $item->gate || Auth::user()->can($item->gate);
        });
    }

    /**
     * Get the sections available.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSections()
    {
        return $this->sections->filter(function ($section) {
            return ! $section->gate || Auth::user()->can($section->gate);
        });
    }

    /**
     * Get the groups available.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGroups()
    {
        return $this->groups->filter(function ($group) {
            return ! $group->gate || Auth::user()->can($group->gate);
        });
    }

    public function hasLinks(): bool
    {
        if ($this->getItems()->count()) {
            return true;
        }

        if ($this->getGroups()->filter(fn ($group) => $group->hasLinks())->count()) {
            return true;
        }

        if ($this->getSections()->filter(fn ($section) => $section->hasLinks())->count()) {
            return true;
        }

        return false;
    }

    public function getFirstLink()
    {
        if ($firstItem = $this->getItems()->first()) {
            return $firstItem;
        }

        if ($firstGroupItem = $this->getGroups()->first()?->getFirstLink()) {
            return $firstGroupItem;
        }

        if ($firstSectionItem = $this->getSections()->first()?->getFirstLink()) {
            return $firstSectionItem;
        }

        return null;
    }

    /**
     * Get the handle of the slot.
     *
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Get an existing or create a new section on the slot.
     *
     * @param  string  $handle
     * @return \Lunar\Hub\Menu\MenuSection
     */
    public function section($handle)
    {
        $section = $this->sections->first(function ($section) use ($handle) {
            return $section->getHandle() == $handle;
        });

        if ($section) {
            return $section;
        }

        $section = new MenuSection($handle);

        $this->sections->push($section);

        return $section;
    }

    /**
     * Get an existing or create a new section on the slot.
     *
     * @param  string  $handle
     * @return \Lunar\Hub\Menu\MenuGroup
     */
    public function group($handle)
    {
        $group = $this->groups->first(function ($group) use ($handle) {
            return $group->getHandle() == $handle;
        });

        if ($group) {
            return $group;
        }

        $group = new MenuGroup($handle);

        $this->groups->push($group);

        return $group;
    }
}
