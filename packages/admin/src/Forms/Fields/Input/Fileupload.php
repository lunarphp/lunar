<?php

namespace Lunar\Hub\Forms\Fields\Input;

use Lunar\Hub\Forms\InputField;

class Fileupload extends InputField
{
    /**
     * Whether or not the input has an error to show.
     *
     * @var bool
     */
    public bool $error = false;

    /**
     * Specify any filetypes for validation client side.
     *
     * @var array
     */
    public $filetypes = [];

    /**
     * Initialise the component.
     *
     * @param  bool  $error
     */
    public function __construct($error = false, $filetypes = [])
    {
        $this->error = $error;
        $this->filetypes = $filetypes;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.fileupload');
    }
}
