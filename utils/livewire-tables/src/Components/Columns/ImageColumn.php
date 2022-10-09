<?php

namespace Lunar\LivewireTables\Components\Columns;

class ImageColumn extends BaseColumn
{
    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('l-tables::columns.image', [
            'url' => $this->url,
            'record' => $this->record,
            'value' => $this->getValue(),
        ]);
    }
}
