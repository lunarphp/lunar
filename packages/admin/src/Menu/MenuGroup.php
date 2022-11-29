<?php

namespace Lunar\Hub\Menu;

use Illuminate\Support\Str;
use Lunar\Hub\LunarHub;

class MenuGroup extends MenuSlot
{
    /**
     * The display name of the menu group.
     *
     * @var string
     */
    public $name;

    /**
     * The display name of the menu group.
     *
     * @var string
     */
    public $handle;

    /**
     * Setter for the name property.
     *
     * @param  string  $name
     * @return static
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Render the HTML for the icon.
     *
     * @param  string  $attrs
     * @return string
     */
    public function renderIcon($attrs = null)
    {
        return LunarHub::icon($this->icon, $attrs);
    }

    /**
     * Determines whether this menu group is considered active.
     *
     * @param  string  $path
     * @return bool
     */
    public function isActive($path)
    {
        return Str::startsWith($path, $this->handle);
    }
}
