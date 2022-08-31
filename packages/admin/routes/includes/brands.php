<?php

use GetCandy\Hub\Http\Livewire\Pages\Brands\BrandsIndex;
use GetCandy\Hub\Http\Livewire\Pages\Brands\BrandShow;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'can:catalogue:manage-products',
], function () {
    Route::get('/', BrandsIndex::class)->name('hub.brands.index');
    Route::get('{brand}', BrandShow::class)->name('hub.brands.show');
});
