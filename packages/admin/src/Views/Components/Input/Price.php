<?php

namespace GetCandy\Hub\Views\Components\Input;

use Illuminate\View\Component;

class Price extends Component
{
    /**
     * Whether or not the input has an error to show.
     *
     * @var bool
     */
    public bool $error = false;

    public $symbol;

    public $currencyCode;

    /**
     * Initialise the component.
     *
     * @param bool $error
     */
    public function __construct($symbol, $currencyCode, $error = false)
    {
        $this->symbol = $symbol;
        $this->currencyCode = $currencyCode;
        $this->error = $error;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.price');
    }
}
