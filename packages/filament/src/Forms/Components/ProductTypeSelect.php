<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\ProductType;

class ProductTypeSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.product_type.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.product_type.placeholder'));
        $this->relationship('productType', 'name');
        $this->searchable();
        $this->preload();
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return ProductType::class;
    }
}
