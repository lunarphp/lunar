<?php

namespace Lunar\Admin\Support\DataTransferObjects;

class Role
{
    /**
     * Initialise the role.
     */
    public function __construct(public string $handle, public bool $firstParty = true)
    {
        //
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

    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        }
    }

    public static function make(string $handle, bool $firstParty = true): static
    {
        return app(static::class, ['handle' => $handle, 'firstParty' => $firstParty]);
    }

    public function transLabel(): string
    {
        $key = "lunarpanel::auth.roles.{$this->handle}.label";
        $trans = __($key);

        return $trans == $key ? $this->handle : $trans;
    }

    public function transDescription(): string
    {
        $key = "lunarpanel::auth.roles.{$this->handle}.description";
        $trans = __($key);

        return $trans == $key ? $this->handle : $trans;
    }
}
