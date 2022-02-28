<?php

namespace GetCandy\Hub\Views\Components\Orders;

use Illuminate\View\Component;

class Status extends Component
{
    /**
     * The status string.
     *
     * @var string
     */
    public string $status = '';

    /**
     * The status label.
     *
     * @var string
     */
    public string $label;

    /**
     * The status color.
     *
     * @var string
     */
    public string $color;

    /**
     * Initialise the component.
     *
     * @param  string  $email
     */
    public function __construct($status)
    {
        $statuses = config('getcandy.orders.statuses');

        $match = $statuses[$status] ?? null;

        $this->label = $match['label'] ?? $status;
        $this->color = $match['color'] ?? '#f5f5f5';
        $this->status = $status;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.orders.status');
    }
}
