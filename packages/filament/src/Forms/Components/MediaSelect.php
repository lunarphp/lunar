<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;

class MediaSelect extends Select
{
    protected string $view = 'lunar-filament::forms.components.media-select';

    protected function setUp(): void
    {
        parent::setUp();
    }
}
