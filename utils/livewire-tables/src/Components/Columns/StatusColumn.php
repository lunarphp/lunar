<?php

namespace Lunar\LivewireTables\Components\Columns;

class StatusColumn extends BaseColumn
{
    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('l-tables::columns.status', [
            'url' => $this->url,
            'record' => $this->record,
            'value' => $this->getValue(),
        ]);
    }
}
