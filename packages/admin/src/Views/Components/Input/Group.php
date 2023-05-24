<?php

namespace Lunar\Hub\Views\Components\Input;

use Illuminate\View\Component;

class Group extends Component
{
    /**
     * The label for the group.
     *
     * @var string
     */
    public $label;

    /**
     * Specify what input this label is bound to.
     *
     * @var string
     */
    public $for;

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
     * Any instructions which should be rendered.
     *
     * @var string
     */
    public $instructions;

    /**
     * Whether this input group is required.
     */
    public bool $required = false;

    /**
     * Determine whether error icon should be shown.
     */
    public bool $errorIcon = true;

    /**
     * Create the component instance.
     *
     * @param  string  $label
     * @param  string  $for
     * @param  string  $error
     * @param  string  $instructions
     */
    public function __construct($label, $for, $error = null, $instructions = null, $required = false, $errors = [], $errorIcon = true)
    {
        $this->label = $label;
        $this->for = $for;
        $this->error = $error;
        $this->instructions = $instructions;
        $this->required = $required;
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
        return view('adminhub::components.input.group');
    }
}
