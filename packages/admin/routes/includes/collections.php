<?php

use Illuminate\Support\Facades\Route;
use Lunar\Hub\Http\Livewire\Pages\Collections\CollectionGroupShow;
use Lunar\Hub\Http\Livewire\Pages\Collections\CollectionGroupsIndex;
use Lunar\Hub\Http\Livewire\Pages\Collections\CollectionShow;

/**
 * Channel routes.
 */
Route::group([
    'middleware' => 'can:catalogue:manage-collections',
], function () {
    Route::group([
        'prefix' => 'collection-groups',
    ], function () {
        Route::get('/', CollectionGroupsIndex::class)->name('hub.collection-groups.index');

        Route::group([
            'prefix' => '{group}',
        ], function () {
            Route::get('/', CollectionGroupShow::class)->name('hub.collection-groups.show');

            Route::get('/collections/{collection}', CollectionShow::class)->name('hub.collections.show');
        });
    });
});
