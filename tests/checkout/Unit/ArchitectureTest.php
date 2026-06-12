<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Action conventions (spec 0016)
|--------------------------------------------------------------------------
|
| Every checkout action implements a contract, exposes a single `execute()`
| entry point, and injects its collaborators rather than reaching for a
| facade.
|
*/

arch('checkout actions implement a contract')
    ->expect('Lunar\Checkout\Actions')
    ->classes()
    ->not->toImplementNothing();

arch('checkout actions expose an execute method')
    ->expect('Lunar\Checkout\Actions')
    ->classes()
    ->toHaveMethod('execute');

arch('checkout actions do not depend on facades')
    ->expect('Lunar\Checkout\Actions')
    ->not->toUse([
        'Illuminate\Support\Facades',
        'Lunar\Core\Facades',
        'Lunar\Checkout\Facades',
    ]);
