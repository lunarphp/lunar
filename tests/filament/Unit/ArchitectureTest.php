<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/*
|--------------------------------------------------------------------------
| Standalone bridge
|--------------------------------------------------------------------------
|
| Every class and view in lunarphp/filament must work in any Filament v5
| panel without lunarphp/admin installed. The dependency runs one way:
| the admin shell builds on the bridge, never the reverse.
|
*/

arch('the bridge does not depend on the admin shell')
    ->expect('Lunar\Filament')
    ->not->toUse('Lunar\Admin');

test('bridge views do not reference the admin shell', function () {
    $views = Finder::create()
        ->files()
        ->in(dirname(__DIR__, 3).'/packages/filament/resources/views');

    foreach ($views as $view) {
        expect($view->getContents())
            ->not->toContain('Lunar\Admin', "{$view->getRelativePathname()} references the admin shell");
    }
});
