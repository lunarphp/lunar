<?php

namespace Lunar\LivewireTables\Components\Columns;

use Closure;

class BadgeColumn extends BaseColumn
{
    public ?Closure $states = null;

    public function states(Closure $states)
    {
        $this->states = $states;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $states = [
            'success' => false,
            'warning' => false,
            'danger' => false,
            'info' => false,
        ];

        if ($this->states) {
            $states = call_user_func($this->states, $this->record);
        }

        return view('l-tables::columns.badge', array_merge([
            'record' => $this->record,
            'value' => $this->getValue(),
        ], $states));
    }
}
