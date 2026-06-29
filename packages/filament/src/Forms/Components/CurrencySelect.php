<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Currency;

class CurrencySelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.currency.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.currency.placeholder'));
        $this->relationship('currency', 'name');
        $this->preload();
        $this->default(fn () => Currency::getDefault()?->getKey());
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Currency::class;
    }
}
