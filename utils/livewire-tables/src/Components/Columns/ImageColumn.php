<?php

namespace Lunar\LivewireTables\Components\Columns;

use Lunar\LivewireTables\TableManifest;
use Livewire\Component;

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
