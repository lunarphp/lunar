<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\Channel;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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
