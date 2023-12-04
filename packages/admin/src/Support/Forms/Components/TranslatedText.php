<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\TextInput;
use Lunar\Models\Language;

class TranslatedText extends TextInput
{
    protected string $view = 'lunarpanel::forms.components.translated-text';

    public function getLanguages()
    {
        // TODO: blink + sort
        return Language::get();
    }
}
