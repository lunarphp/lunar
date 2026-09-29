<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Route;
use Lunar\SearchRelevance\Http\Controllers\SearchEventController;
use Lunar\SearchRelevance\Http\Middleware\ReadOnlySession;

/*
 * Not in the `web` group: that group saves the session at the end of the
 * request, which races the request the beacon accompanies (see
 * ReadOnlySession). Without a saved session there is no CSRF token to check
 * either; the endpoint always answers 204 and is rate limited.
 */
Route::post('lunar/search/events', SearchEventController::class)
    ->middleware([EncryptCookies::class, ReadOnlySession::class, 'throttle:lunar-search-relevance-events'])
    ->name('lunar.search-relevance.events');
