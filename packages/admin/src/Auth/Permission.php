<?php

namespace Lunar\Hub\Auth;

use Illuminate\Support\Collection;

class Permission
{
    /**
     * The display name for the permission.
     *
     * @var string
     */
    public $name;

    /**
     * The unique handle for the permission.
     *
     * @var string
     */
    public $handle;

    /**
     * The display description for the permissions.
     *
     * @var string
     */
    public $description;

    /**
     * Whether this is a first party permission, set by Lunar.
     */
    public bool $firstParty = true;

    /**
     * A collection of related, children permissions.
     */
    public Collection $children;

    /**
     * Initialise the permission.
     *
     * @param  string  $name
     * @param  string  $handle
     * @param  string  $description
     * @param  bool  $firstParty
     */
    public function __construct($name = null, $handle = null, $description = null, $firstParty = true)
    {
        $this->name = $name;
        $this->handle = $handle;
        $this->description = $description;
        $this->firstParty = $firstParty;
        $this->children = collect();
    }

    /**
     * Magic method used for setting properties via methods.
     *
     * @param  string  $name
     * @param  array  $params
     * @return static
     */
    public function __call($name, $params)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $params[0];
        }

        return $this;
    }
}
