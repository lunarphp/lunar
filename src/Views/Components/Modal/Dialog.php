<?php

namespace GetCandy\Hub\Views\Components\Modal;

use Illuminate\View\Component;

class Dialog extends Component
{
    public $id = null;

    public $maxWidth = null;

    public $form = null;

    public function __construct($id = null, $maxWidth = null, $form = null)
    {
        $this->id = $id;
        $this->maxWidth = $maxWidth;
        $this->form = $form;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.modal.dialog');
    }
}
