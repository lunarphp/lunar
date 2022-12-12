<?php

namespace Lunar\LivewireTables\Components\Columns;

class TagsColumn extends BaseColumn
{
    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('l-tables::columns.tags', [
            'record' => $this->record,
            'value' => $this->getValue(),
        ]);
    }
}
