<?php

use Illuminate\Support\Facades\Route;
use Lunar\Hub\Http\Livewire\Pages\Products\ProductTypes\ProductTypeCreate;
use Lunar\Hub\Http\Livewire\Pages\Products\ProductTypes\ProductTypeIndex;
use Lunar\Hub\Http\Livewire\Pages\Products\ProductTypes\ProductTypeShow;

Route::group([
    'middleware' => 'can:catalogue:manage-products',
], function () {
    Route::get('/', ProductTypeIndex::class)->name('hub.product-types.index');
    Route::get('create', ProductTypeCreate::class)->name('hub.product-types.create');
    Route::get('{productType}', ProductTypeShow::class)->name('hub.product-types.show');
});
