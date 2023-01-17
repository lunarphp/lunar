<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;
use Lunar\Models\Language;

class Errors extends Component
{
    /**
     * The error for this input group.
     *
     * @var string
     */
    public $error;

    /**
     * An array of validation errors.
     *
     * @var array
     */
    public $errors = [];

    /**
     * Determine whether error icon should be shown.
     *
     * @var bool
     */
    public bool $errorIcon = true;

    /**
     * Create a new instance of the component.
     *
     * @param  bool  $expanded
     */
    public function __construct($error = null, $errors = [], $errorIcon = true)
    {
        $this->error = $error;
        $this->errors = $errors;
        $this->errorIcon = $errorIcon;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.errors');
    }
}
