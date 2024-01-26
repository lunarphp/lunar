<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\RichEditor as RichEditorComponent;

class TranslatedRichEditor extends RichEditorComponent
{
    public function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel();
    }
}