<?php

use Lunar\Filament\LunarFilamentServiceProvider;
use Lunar\Filament\Support\ComponentExtensions\Registry;
use Lunar\Filament\Support\Facades\LunarFilament;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('binds the component extensions registry as a singleton', function () {
    expect(app(Registry::class))->toBeInstanceOf(Registry::class)
        ->and(app(Registry::class))->toBe(app(Registry::class))
        ->and(app('lunar-filament.extensions'))->toBe(app(Registry::class));
});

it('registers extensions against bridge target classes', function () {
    $registry = new Registry;

    $extensionA = new class
    {
        public function whatever(): string
        {
            return 'a';
        }
    };

    $registry->register(['Foo' => $extensionA]);

    expect($registry->for('Foo'))->toHaveCount(1)
        ->and($registry->for('Foo')[0])->toBe($extensionA);
});

it('exposes the service provider via package discovery', function () {
    expect(app()->getLoadedProviders())->toHaveKey(LunarFilamentServiceProvider::class);
});

it('exposes the lunar-filament config namespace', function () {
    expect(config('lunar.filament'))->toBeArray()
        ->and(config('lunar.filament.resolver.app_namespace'))->toBe('App\\Filament');
});

it('defaults the config keys relocated from lunar.admin', function () {
    expect(config('lunar.filament.enable_variants'))->toBeTrue()
        ->and(config('lunar.filament.pdf_rendering'))->toBe('download')
        ->and(config('lunar.filament.scout_enabled'))->toBeFalse();
});

it('boots a LunarFilament facade backed by the registry', function () {
    $extension = new class
    {
        public function dummy(): string
        {
            return 'dummy';
        }
    };

    LunarFilament::extensions(['Target' => $extension]);

    expect(LunarFilament::for('Target'))->toHaveCount(1)
        ->and(LunarFilament::for('Target')[0])->toBe($extension);
});
