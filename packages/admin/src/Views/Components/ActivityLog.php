<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class ActivityLog extends Component
{
    /**
     * The subject type for the logs.
     *
     * @var string
     */
    public Model $subject;

    /**
     * Initialise the component.
     *
     * @param  string  $level
     */
    public function __construct($subject)
    {
        dd($subject);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.activity-log');
    }
}
