<?php

use GetCandy\Hub\Http\Livewire\Pages\Collections\CollectionGroupShow;
use GetCandy\Hub\Http\Livewire\Pages\Collections\CollectionGroupsIndex;
use GetCandy\Hub\Http\Livewire\Pages\Collections\CollectionShow;
use GetCandy\Hub\Http\Livewire\Pages\Customers\CustomersIndex;
use Illuminate\Support\Facades\Route;

/**
 * Channel routes.
 */
Route::group([
    'middleware' => 'can:catalogue:manage-customers',
], function () {
    Route::get('/', CustomersIndex::class)->name('hub.customers.index');
});
