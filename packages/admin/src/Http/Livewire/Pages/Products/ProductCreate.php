<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Products;

class ProductCreate extends Resource
{
    protected static string $view = 'products.create';

    public function mount()
    {
        static::setTitle('Create Product');
    }
}
