<?php

use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Auth\AuthenticatedSessionController;
use Lunar\Panel\Http\Controllers\Auth\NewPasswordController;
use Lunar\Panel\Http\Controllers\Auth\PasswordResetLinkController;
use Lunar\Panel\Http\Controllers\Auth\TwoFactorChallengeController;
use Lunar\Panel\Http\Controllers\TranslationsController;
use Lunar\Panel\Http\Middleware\RedirectIfAuthenticated;

// Registered outside the `RedirectIfAuthenticated` group (and outside the
// authenticated web.php group) so it serves guests on the login/2FA/reset
// screens and authenticated staff alike from a single route.
Route::get('translations/{locale}', TranslationsController::class)
    ->where('locale', '[A-Za-z_]+')
    ->name('panel.translations');

Route::middleware(RedirectIfAuthenticated::class)->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('panel.login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('panel.login.store');

    Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('panel.two-factor.challenge');
    Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->name('panel.two-factor.challenge.store');
    Route::post('two-factor-challenge/resend', [TwoFactorChallengeController::class, 'resend'])->name('panel.two-factor.challenge.resend');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('panel.password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('panel.password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('panel.password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('panel.password.store');
});
