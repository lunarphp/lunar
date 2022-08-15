<?php

namespace GetCandy\Hub\Forms;

use Illuminate\View\Component;

abstract class InputField extends Component
{
    protected string $field;

    protected string $label;

    protected bool $required = false;

    public function make(string $name): static
    {
        $this->field = $name;

        // @todo Add default label translation

        return resolve(static::class, ['name' => $name]);
    }

    public function required(bool $condition = true): static
    {
        $this->required = $condition;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    abstract public function render();
}
