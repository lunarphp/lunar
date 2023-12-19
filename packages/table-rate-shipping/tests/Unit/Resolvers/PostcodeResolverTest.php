<?php

uses(\Lunar\Shipping\Tests\TestCase::class);
use Lunar\Shipping\Resolvers\PostcodeResolver;

test('can get postcode query parts', function () {
    $postcode = 'ABC 123';

    $parts = (new PostcodeResolver())->getParts($postcode);

    expect($parts)->toContain('ABC123');
    expect($parts)->toContain('ABC');
    expect($parts)->toContain('AB');

    $postcode = 'NW1 1TX';

    $parts = (new PostcodeResolver())->getParts($postcode);

    expect($parts)->toContain('NW11TX');
    expect($parts)->toContain('NW1');
    expect($parts)->toContain('NW');

    $postcode = 90210;

    $parts = (new PostcodeResolver())->getParts($postcode);
    expect($parts)->toContain('90210');
    expect($parts)->toContain('90');
});
