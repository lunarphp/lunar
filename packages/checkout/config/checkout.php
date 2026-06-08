<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Checkout route
    |--------------------------------------------------------------------------
    |
    | The URI the checkout page is served from and the middleware applied to
    | the checkout route group. This is the mount point a consuming storefront
    | drops the checkout onto.
    |
    */

    'path' => 'checkout',

    'middleware' => ['web'],

];
