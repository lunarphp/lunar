<?php

namespace Lunar\Hub\Views\Components\Orders;

use Illuminate\View\Component;

class Status extends Component
{
    /**
     * The status string.
     *
     * @var string
     */
    public ?string $status = '';

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
    public function __construct($record = null, $status = null)
    {
        if ($record && ! $status) {
            $status = $record->status;
        }

        $statuses = config('lunar.orders.statuses');

        $match = $statuses[$status] ?? null;

        $this->label = $match['label'] ?? 'N/A';
        $this->color = $match['color'] ?? '#7C7C7C';
        $this->status = $record?->status ?: $status;
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
