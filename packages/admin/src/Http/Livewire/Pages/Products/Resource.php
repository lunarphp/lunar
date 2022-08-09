<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Products;

use GetCandy\Hub\Http\Livewire\PageComponent;

abstract class Resource extends PageComponent
{
    protected static string $navigationLabel = 'Products';

    protected static string $navigationIcon = 'shopping-cart';

    protected static string $navigationGroup = 'catalogue';

    public function mount()
    {
        static::setTitle('Products');
        static::setParams([
            'filters' => [],
        ]);
    }
}
