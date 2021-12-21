<?php

use GetCandy\Hub\Http\Livewire\Pages\Products\ProductTypes\ProductTypeCreate;
use GetCandy\Hub\Http\Livewire\Pages\Products\ProductTypes\ProductTypeIndex;
use GetCandy\Hub\Http\Livewire\Pages\Products\ProductTypes\ProductTypeShow;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'can:catalogue:manage-products',
], function () {
    Route::get('/', ProductTypeIndex::class)->name('hub.product-types.index');
    Route::get('create', ProductTypeCreate::class)->name('hub.product-type.create');
    Route::get('{productType}', ProductTypeShow::class)->name('hub.product-type.show');
});
