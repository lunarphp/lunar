<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\Toggle as ToggleComponent;

class Toggle extends ToggleComponent
{
    public function setUp(): void
    {
        parent::setUp();

        $this->rules = [];
    }
}
