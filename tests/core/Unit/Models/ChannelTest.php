<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\Channel;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a channel', function () {
    $channel = Channel::factory()->create([
        'name' => 'Webstore',
        'handle' => 'webstore',
        'default' => true,
        'url' => 'http://mystore.test',
    ]);

    expect($channel->name)->toEqual('Webstore');
    expect($channel->handle)->toEqual('webstore');
    expect($channel->default)->toBeTrue();
    expect($channel->url)->toEqual('http://mystore.test');
});

test('changes are recorded in activity log', function () {
    activity()->enableLogging();

    $channel = Channel::factory()->create([
        'name' => 'Webstore',
    ]);

    $channel->update([
        'name' => 'Foobar',
    ]);

    $log = $channel->activities()->whereEvent('updated')->first();

    expect($log)->not->toBeNull();
});
