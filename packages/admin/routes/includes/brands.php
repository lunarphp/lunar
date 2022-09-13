<?php

use Illuminate\Support\Facades\Route;
use Lunar\Hub\Http\Livewire\Pages\Brands\BrandShow;
use Lunar\Hub\Http\Livewire\Pages\Brands\BrandsIndex;

Route::group([
    'middleware' => 'can:catalogue:manage-products',
], function () {
    Route::get('/', BrandsIndex::class)->name('hub.brands.index');
    Route::get('{brand}', BrandShow::class)->name('hub.brands.show');
});
