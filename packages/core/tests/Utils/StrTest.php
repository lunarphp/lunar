<?php

uses(\Lunar\Tests\TestCase::class);
use Illuminate\Support\Str;

test('passing kebab case string', function () {
    expect(Str::handle('foo-bar'))->toEqual('foo_bar');
});

test('passing sentence string', function () {
    expect(Str::handle('foo bar'))->toEqual('foo_bar');
});

test('passing mixed sentence and kebab case', function () {
    expect(Str::handle('foo-bar foo bar'))->toEqual('foo_bar_foo_bar');
});
