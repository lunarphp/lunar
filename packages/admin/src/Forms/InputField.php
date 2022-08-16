<?php

namespace GetCandy\Hub\Forms;

use Illuminate\View\Component;

abstract class InputField extends Component
{
    use Traits\CanResolveFromContainer;

    public string $name;

    public string $modelName;

    public string $label;

    public bool $required = false;

    public function __construct(string $name)
    {
        $this->modelName = 'model.'.$name;
        $this->name = $name;
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
