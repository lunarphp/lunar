<?php

use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Account\ConfirmTwoFactorController;
use Lunar\Panel\Http\Controllers\Account\PasswordController;
use Lunar\Panel\Http\Controllers\Account\RecoveryCodesController;
use Lunar\Panel\Http\Controllers\Account\SecurityController;
use Lunar\Panel\Http\Controllers\Account\TwoFactorController;
use Lunar\Panel\Http\Controllers\Auth\AuthenticatedSessionController;
use Lunar\Panel\Http\Controllers\DashboardController;

Route::get('/', DashboardController::class)->name('panel.dashboard');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('panel.logout');

Route::get('account/security', [SecurityController::class, 'edit'])->name('panel.account.security');
Route::put('account/password', [PasswordController::class, 'update'])->name('panel.account.password.update');
Route::post('account/two-factor', [TwoFactorController::class, 'store'])->name('panel.account.two-factor.store');
Route::delete('account/two-factor', [TwoFactorController::class, 'destroy'])->name('panel.account.two-factor.destroy');
Route::post('account/two-factor/confirm', [ConfirmTwoFactorController::class, 'store'])->name('panel.account.two-factor.confirm');
Route::post('account/two-factor/recovery-codes', [RecoveryCodesController::class, 'store'])->name('panel.account.two-factor.recovery-codes');
