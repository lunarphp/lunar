<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DemoData\Support\DemoContext;
use Lunar\Tests\DemoData\TestCase;

use function Pest\Laravel\artisan;

uses(TestCase::class, RefreshDatabase::class);

test('the demo-data config is merged under lunar.demo-data', function () {
    expect(config('lunar.demo-data.default_scale'))->toBe('small');
    expect(config('lunar.demo-data.scales'))->toHaveKeys(['small', 'medium', 'large']);
});

test('lunar:demo-data runs at the default scale', function () {
    artisan('lunar:demo-data')
        ->expectsOutputToContain('scale: small')
        ->expectsOutputToContain('Demo data seeded.')
        ->assertExitCode(0);
});

test('lunar:demo-data accepts an explicit scale', function () {
    artisan('lunar:demo-data', ['--scale' => 'large'])
        ->expectsOutputToContain('scale: large')
        ->assertExitCode(0);
});

test('lunar:demo-data rejects an unknown scale', function () {
    artisan('lunar:demo-data', ['--scale' => 'enormous'])
        ->assertExitCode(1);
});

test('a context built from config carries the scale counts and a seeded faker', function () {
    $context = DemoContext::fromConfig('medium');

    expect($context->scale)->toBe('medium');
    expect($context->count('products'))->toBe(50);
    expect($context->faker->numberBetween(1, 1_000_000))
        ->toBe(DemoContext::fromConfig('medium')->faker->numberBetween(1, 1_000_000));
});
