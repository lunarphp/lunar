<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Lunar\Paypal\Http\Controllers\WebhookController;
use Lunar\Paypal\Http\Middleware\PaypalWebhookMiddleware;

Route::post(config('lunar.paypal.webhook_path', 'paypal/webhook'), WebhookController::class)
    ->middleware([PaypalWebhookMiddleware::class, 'api'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('lunar.paypal.webhook');
