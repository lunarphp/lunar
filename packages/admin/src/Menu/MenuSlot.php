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
     * Initialise the class.
     *
     * @param  string  $handle
     */
    public function __construct($handle)
    {
        $this->handle = $handle;
        $this->items = collect();
        $this->sections = collect();
    }

    /**
     * Add an item to the menu slot.
     *
     * @param  \Closure  $callback
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

        if ($index) {
            $this->items->splice($index + 1, 0, [$item]);

            return $this;
        }

        $this->items->push($item);

        return $this;
    }

    /**
     * Add multiple items.
     *
     * @param  array  $items
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
     *
     * @return \Illuminate\Support\Collection
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
        return $this->sections;
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
     * Remove an existing menu item on the slot.
     *
     * @param  string  $handle
     * @return \Lunar\Hub\Menu\MenuSlot
     */
    public function removeItem($handle)
    {
        $newItems = $this->items->filter(function ($item) use ($handle) {
            return $item->handle != $handle;
        });

        $this->items = $newItems;

        return $this;
    }

    /**
     * Remove an existing menu section on the slot.
     *
     * @param  string  $handle
     * @return \Lunar\Hub\Menu\MenuSlot
     */
    public function removeSection($handle)
    {
        $newSections = $this->sections->filter(function ($section) use ($handle) {
            return $section->handle != $handle;
        });

        $this->sections = $newSections;

        return $this;
    }

    /**
     * Remove an existing menu item from an
     * existing menu section on the slot.
     *
     * @param  string  $handle
     * @param  string  $itemHandle
     * @return \Lunar\Hub\Menu\MenuSlot
     */
    public function removeSectionItem($handle, $itemHandle)
    {
        $foundSection = $this->sections->first(function ($section) use ($handle) {
            return $section->handle == $handle;
        });

        $foundSection->items = $foundSection->items->filter(function ($item) use ($itemHandle) {
            return $item->handle != $itemHandle;
        });

        return $this;
    }

    /**
     * Update an existing menu item on the slot.
     *
     * @param  string  $handle
     * @param  array  $options
     * @return \Lunar\Hub\Menu\MenuItem
     */
    public function updateItem($handle, $options = [])
    {
        $foundItem = $this->items->first(function ($item) use ($handle) {
            return $item->handle == $handle;
        });

        collect($options)->each(function ($value, $key) use ($foundItem) {
            $foundItem->{$key} = $value;
        });

        return $foundItem;
    }

    /**
     * Update an existing menu section on the slot.
     *
     * @param  string  $handle
     * @param  array  $options
     * @return \Lunar\Hub\Menu\MenuSection
     */
    public function updateSection($handle, $options = [])
    {
        $foundSection = $this->sections->first(function ($item) use ($handle) {
            return $item->handle == $handle;
        });

        collect($options)->each(function ($value, $key) use ($foundSection) {
            $foundSection->{$key} = $value;
        });

        return $foundSection;
    }

    /**
     * Update an existing menu item from an
     * existing menu section on the slot.
     *
     * @param  string  $handle
     * @param  string  $itemHandle
     * @param  array  $options
     * @return \Lunar\Hub\Menu\MenuItem
     */
    public function updateSectionItem($handle, $itemHandle, $options = [])
    {
        $foundSection = $this->sections->first(function ($section) use ($handle) {
            return $section->handle == $handle;
        });

        $foundItem = $foundSection->items->first(function ($item) use ($itemHandle) {
            return $item->handle == $itemHandle;
        });

        collect($options)->each(function ($value, $key) use ($foundItem) {
            $foundItem->{$key} = $value;
        });

        return $foundItem;
    }
}
