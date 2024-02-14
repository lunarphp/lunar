<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\RichEditor;

class TranslatedRichEditor extends RichEditor
{
    public function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel();
    }
}
