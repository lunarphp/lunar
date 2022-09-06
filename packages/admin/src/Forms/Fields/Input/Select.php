<?php

namespace GetCandy\Hub\Forms\Fields\Input;

use GetCandy\Hub\Forms\InputField;

class Select extends InputField
{
    /**
     * Whether or not the input has an error to show.
     *
     * @var bool
     */
    public bool $error = false;

    /**
     * The options for the select.
     *
     * @var array
     */
    public array $options = [];

    /**
     * Initialise the component.
     *
     * @param  bool  $error
     */
    public function __construct(string $name, $error = false)
    {
        parent::__construct($name);

        $this->error = $error;
    }

    public function options(array $options, bool $relationship = false): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.select');
    }
}
