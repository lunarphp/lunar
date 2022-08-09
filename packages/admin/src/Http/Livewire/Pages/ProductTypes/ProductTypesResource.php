<?php

namespace GetCandy\Hub\Http\Livewire\Pages\ProductTypes;

use GetCandy\Hub\Http\Livewire\PageComponent;

abstract class ProductTypesResource extends PageComponent
{
    protected static string $navigationLabel = 'Product Types';

    protected static string $navigationIcon = 'shopping-cart';

    protected static string $navigationGroup = 'catalogue';

    public function mount()
    {
        static::setTitle('Product Types');
        static::setParams([
            'filters' => [],
        ]);
    }
}
