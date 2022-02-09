<?php

namespace GetCandy\Hub\Menu;

use Illuminate\Support\Str;

class MenuSection extends MenuSlot
{
    /**
     * The display name of the menu section.
     *
     * @var string
     */
    public $name;

    /**
     * Initialise the class.
     *
     * @param  string  $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->handle = Str::slug($name);
        $this->items = collect();
        $this->sections = collect();
    }

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
}
