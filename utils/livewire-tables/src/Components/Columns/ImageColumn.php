<?php

namespace Lunar\LivewireTables\Components\Columns;

use Livewire\Component;
use Lunar\LivewireTables\TableManifest;

class ImageColumn extends BaseColumn
{
    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('tables::columns.image', [
            'url' => $this->url,
            'record' => $this->record,
            'value' => $this->getValue(),
        ]);
    }
}
