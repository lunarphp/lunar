<?php

namespace Lunar\Admin\Support\Concerns;

use Filament\Forms\Form;

trait ExtendsForms
{
    public static function form(Form $form): Form
    {
        return self::callLunarHook('extendForm', static::getDefaultForm($form));
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema(static::getMainFormComponents());
    }

    protected static function getMainFormComponents(): array
    {
        return [];
    }
}
