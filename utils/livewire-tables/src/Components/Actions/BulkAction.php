<?php

namespace GetCandy\LivewireTables\Components\Actions;

class BulkAction extends Action
{
    public function render()
    {
        return view('tables::actions.bulk', [
            'label' => $this->label,
            'record' => $this->record,
        ]);
    }
}
