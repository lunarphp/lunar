<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;

class Notification extends Component
{
    /**
     * An array of messages to show.
     *
     * @var array
     */
    public array $messages = [];

    /**
     * Define the level of the notification.
     *
     * @var string
     */
    public $level = 'success';

    /**
     * Initialise the component.
     */
    public function __construct($level = 'success')
    {
        if ($message = session()->get('notify.message')) {
            $this->messages[] = $message;
        }
        $this->level = session()->get('notify.level', 'success');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.notification');
    }
}
