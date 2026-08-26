<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Channel;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can get default record with scope', function () {
    $defaultChannel = Channel::factory()->create([
        'default' => true,
    ]);

    Channel::factory(10)->create([
        'default' => false,
    ]);

    expect(Channel::default()->first()->id)->toEqual($defaultChannel->id);
});

test('can get default record with static helper', function () {
    $defaultChannel = Channel::factory()->create([
        'default' => true,
    ]);

    Channel::factory(10)->create([
        'default' => false,
    ]);

    expect(Channel::getDefault()->id)->toEqual($defaultChannel->id);
});

test('can return null when no record is marked as default', function () {
    Channel::factory(3)->create([
        'default' => false,
    ]);

    expect(Channel::getDefault())->toBeNull();
});
