<?php

use GetCandy\Hub\Http\Livewire\Pages\ProductTypes\ProductTypeCreate;
use GetCandy\Hub\Http\Livewire\Pages\ProductTypes\ProductTypeShow;
use GetCandy\Hub\Http\Livewire\Pages\ProductTypes\ProductTypesIndex;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'can:catalogue:manage-products',
], function () {
    Route::get('/', ProductTypesIndex::class)->name('hub.product-types.index');
    Route::get('create', ProductTypeCreate::class)->name('hub.product-type.create');
    Route::get('{productType}', ProductTypeShow::class)->name('hub.product-type.show');
});
