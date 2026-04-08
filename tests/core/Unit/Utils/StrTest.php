<?php

use Illuminate\Support\Str;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('passing kebab case string', function () {
    expect(Str::handle('foo-bar'))->toEqual('foo_bar');
});

test('passing sentence string', function () {
    expect(Str::handle('foo bar'))->toEqual('foo_bar');
});

test('passing mixed sentence and kebab case', function () {
    expect(Str::handle('foo-bar foo bar'))->toEqual('foo_bar_foo_bar');
});
