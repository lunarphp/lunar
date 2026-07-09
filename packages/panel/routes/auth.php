<?php

use Illuminate\Support\Facades\Route;

// Stub — the real auth controllers land in spec 0049 slice 3.
Route::get('login', fn () => abort(501))->name('panel.login');
