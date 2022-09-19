<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;

class CartLineModifiers
{
    protected Collection $modifiers;

    public function __construct()
    {
        $this->modifiers = collect();
    }

    public function getModifiers()
    {
        return $this->modifiers;
    }

    public function add($modifier)
    {
        $this->modifiers->push($modifier);
    }

    public function remove($modifier)
    {
        $this->modifiers->forget($modifier);
    }
}
