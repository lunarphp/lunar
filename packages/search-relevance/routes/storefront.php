<?php

use Illuminate\Support\Facades\Route;
use Lunar\SearchRelevance\Http\Controllers\SearchEventController;

Route::post('lunar/search/events', SearchEventController::class)
    ->middleware(['web', 'throttle:lunar-search-relevance-events'])
    ->name('lunar.search-relevance.events');
