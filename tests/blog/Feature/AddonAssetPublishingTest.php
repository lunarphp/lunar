<?php

use Illuminate\Support\ServiceProvider;
use Lunar\Tests\Blog\TestCase;

uses(TestCase::class);

it('registers a per-module vendor:publish tag for the blog panel build', function () {
    $paths = ServiceProvider::pathsToPublish(null, 'lunar-blog-panel-assets');

    expect($paths)->not->toBeEmpty();
    expect(array_values($paths))->toContain(public_path('vendor/lunar-panel/lunar-blog'));
});

it('includes the blog panel build in the aggregate panel-all-assets tag', function () {
    $paths = ServiceProvider::pathsToPublish(null, 'panel-all-assets');

    expect(array_values($paths))->toContain(public_path('vendor/lunar-panel/lunar-blog'));
});
