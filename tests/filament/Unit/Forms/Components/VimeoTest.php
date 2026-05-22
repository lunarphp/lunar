<?php

use Lunar\Filament\Forms\Components\Vimeo;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('can be instantiated without the admin panel booted', function () {
    $component = Vimeo::make('video_url');

    expect($component)->toBeInstanceOf(Vimeo::class)
        ->and($component->getName())->toBe('video_url');
});

it('resolves its blade view from the bridge namespace', function () {
    expect(Vimeo::make('video_url')->getView())
        ->toBe('lunar-filament::forms.components.vimeo');
});
