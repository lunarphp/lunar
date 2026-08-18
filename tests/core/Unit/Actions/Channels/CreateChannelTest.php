<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Channels\CreateChannel;
use Lunar\Core\Models\Channel;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('creates a channel', function () {
    $channel = app(CreateChannel::class)->execute([
        'name' => 'Retail',
        'url' => 'https://retail.example.com',
    ]);

    expect($channel)->toBeInstanceOf(Channel::class);
    expect($channel->exists)->toBeTrue();
    expect($channel->name)->toBe('Retail');
    expect($channel->handle)->toBe('retail');
    expect($channel->url)->toBe('https://retail.example.com');
});

it('makes the channel default when requested', function () {
    $channel = app(CreateChannel::class)->execute([
        'name' => 'Retail',
        'default' => true,
    ]);

    expect($channel->fresh()->default)->toBeTrue();
});

it('unsets default on every other channel when the new channel becomes default', function () {
    $existingDefault = Channel::factory()->create(['default' => true]);

    $channel = app(CreateChannel::class)->execute([
        'name' => 'Retail',
        'default' => true,
    ]);

    expect($existingDefault->fresh()->default)->toBeFalse();
    expect($channel->fresh()->default)->toBeTrue();
    expect(Channel::where('default', true)->count())->toBe(1);
});

it('does not touch other channels default flag when not becoming default', function () {
    $existingDefault = Channel::factory()->create(['default' => true]);

    app(CreateChannel::class)->execute([
        'name' => 'Retail',
    ]);

    expect($existingDefault->fresh()->default)->toBeTrue();
});
