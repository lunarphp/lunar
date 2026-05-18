<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Lunar\Stripe\Http\Controllers\WebhookController;
use Lunar\Stripe\Http\Middleware\StripeWebhookMiddleware;

Route::post(config('lunar.stripe.webhook_path', 'stripe/webhook'), WebhookController::class)
    ->middleware([StripeWebhookMiddleware::class, 'api'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('lunar.stripe.webhook');
