<?php

use Illuminate\Support\ServiceProvider;
use Lunar\Tests\Panel\Fixtures\AddonTestCase;

uses(AddonTestCase::class);

it('registers a per-module vendor:publish tag for the add-on build', function () {
    $paths = ServiceProvider::pathsToPublish(null, 'widgets-addon-panel-assets');

    expect($paths)->toHaveKey(dirname(__DIR__).'/Fixtures/resources/build')
        ->and($paths[dirname(__DIR__).'/Fixtures/resources/build'])
        ->toBe(public_path('vendor/lunar-panel/widgets-addon'));
});

it('includes the add-on build in the aggregate panel-all-assets tag', function () {
    $paths = ServiceProvider::pathsToPublish(null, 'panel-all-assets');

    expect($paths)->toHaveKey(dirname(__DIR__).'/Fixtures/resources/build');
});
