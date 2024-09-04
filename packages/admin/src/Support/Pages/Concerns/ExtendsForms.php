<?php

namespace Lunar\Admin\Support\Pages\Concerns;

use Filament\Forms\Form;

trait ExtendsForms
{
    public function form(Form $form): Form
    {
        return self::callLunarHook('extendForm', $this->getDefaultForm($form));
    }

    public function getDefaultForm(Form $form): Form
    {
        return $form;
    }
}
