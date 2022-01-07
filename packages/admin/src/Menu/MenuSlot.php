<?php

namespace GetCandy\Hub\Menu;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
     * @param string $handle
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
     * @param \Closure $callback
     * @param string   $after
     *
     * @return void
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
     * @param array $items
     *
     * @return void
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
            return !$item->gate || Auth::user()->can($item->gate);
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
     * @param string $handle
     *
     * @return \GetCandy\Hub\Menu\MenuSection
     */
    public function section($handle)
    {
        $section = $this->sections->first(function ($section) use ($handle) {
            return $section->getHandle() == Str::slug($handle);
        });

        if ($section) {
            return $section;
        }

        $section = new MenuSection($handle);
        $this->sections->push($section);

        return $section;
    }
}
