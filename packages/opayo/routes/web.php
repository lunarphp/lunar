<?php

use Illuminate\Http\Request;

Route::get('opayo-threedsecure', function () {
    return view('lunar::opayo.threed-secure-iframe');
})->name('opayo.threed.iframe');

Route::post('opayo-threedsecure-response', function (Request $request) {
    return view('lunar::opayo.threed-secure-response', [
        'cres' => $request->cres,
        'PaRes' => $request->PaRes,
        'md' => $request->md,
        'mdx' => $request->mdx,
    ]);
})->name('opayo.threed.response');
