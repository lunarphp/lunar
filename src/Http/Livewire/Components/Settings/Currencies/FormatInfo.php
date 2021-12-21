<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Currencies;

use LivewireUI\Modal\ModalComponent;

class FormatInfo extends ModalComponent
{
    public function render()
    {
        return view('adminhub::livewire.components.settings.currencies.format-info')
            ->layout('adminhub::layouts.base');
    }
}
