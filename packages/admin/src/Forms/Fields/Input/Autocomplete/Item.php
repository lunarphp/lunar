<?php

namespace Lunar\Hub\Forms\Fields\Input\Autocomplete;

use Lunar\Hub\Forms\InputField;

class Item extends InputField
{
    /**
     * Whether or not the input has an error to show.
     *
     * @var bool
     */
    public bool $error = false;

    /**
     * Initialise the component.
     *
     * @param  bool  $error
     */
    public function __construct($error = false)
    {
        $this->error = $error;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.autocomplete.item');
    }
}
