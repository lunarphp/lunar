<?php

namespace Lunar\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Illuminate\Support\Facades\File;

class Transaction extends Entry
{
    protected string $view = 'lunar-filament::infolists.components.transaction';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function renderPaymentIcons()
    {
        echo File::get(__DIR__.'/../../../resources/icons/payment_icons.svg');
    }
}
