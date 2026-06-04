<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Channel;
use Lunar\Core\States\Channel\Active;
use Lunar\Core\States\Channel\Inactive;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('default state is Active', function () {
    $channel = Channel::factory()->create();

    expect($channel->status)->toBeInstanceOf(Active::class);
});

test('Active can transition to Inactive', function () {
    $channel = Channel::factory()->create();

    $channel->status->transitionTo(Inactive::class);

    expect($channel->fresh()->status)->toBeInstanceOf(Inactive::class);
});

test('Inactive can transition to Active', function () {
    $channel = Channel::factory()->create(['status' => Inactive::$name]);

    $channel->status->transitionTo(Active::class);

    expect($channel->fresh()->status)->toBeInstanceOf(Active::class);
});

test('Active cannot transition to itself', function () {
    $channel = Channel::factory()->create();

    expect(fn () => $channel->status->transitionTo(Active::class))
        ->toThrow(CouldNotPerformTransition::class);
});
