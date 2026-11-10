<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Lunar\SearchRelevance\Http\Controllers\SearchEventController;

// No CSRF token needed: an event only counts for the shopper who made the
// search, so a forged cross-site post adds nothing a direct one couldn't, and
// headless storefronts without the token can post.
Route::post('lunar/search/events', SearchEventController::class)
    ->middleware(['web', 'throttle:lunar-search-relevance-events'])
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('lunar.search-relevance.events');
