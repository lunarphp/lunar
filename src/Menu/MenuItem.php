<?php

namespace GetCandy\Hub\Menu;

interface MenuItem
{
    /**
     * Setter for the name property.
     *
     * @param string $name
     *
     * @return void
     */
    public function name($name);

    /**
     * Setter for the handle property.
     *
     * @param string $handle
     *
     * @return void
     */
    public function handle($handle);

    /**
     * Setter for the gate property.
     *
     * @param string $gate
     *
     * @return void
     */
    public function gate($gate);

    /**
     * Setter for the route property.
     *
     * @param string $route
     *
     * @return void
     */
    public function route($route);

    /**
     * Setter for the icon property.
     *
     * @param string $icon
     *
     * @return void
     */
    public function icon($icon);

    /**
     * Determines whether this menu link is considered active.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isActive($path);

    /**
     * Render the HTML for the icon.
     *
     * @param string $attrs
     *
     * @return string
     */
    public function renderIcon($attrs);
}
