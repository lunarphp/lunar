<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\TaxClass;

class TaxClassSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.tax_class.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.tax_class.placeholder'));
        $this->relationship('taxClass', 'name');
        $this->preload();
        $this->default(fn () => TaxClass::getDefault()?->getKey());
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return TaxClass::class;
    }
}
