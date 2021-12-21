<?php

namespace GetCandy\Base;

use Illuminate\Support\Collection;

class OrderModifiers
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

        return $this;
    }
}
