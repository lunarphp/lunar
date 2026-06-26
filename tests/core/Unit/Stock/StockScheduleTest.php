<?php

use Illuminate\Console\Scheduling\Schedule;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('the expired-reservation sweep is scheduled out of the box', function () {
    $commands = collect(app(Schedule::class)->events())->map(fn ($event) => $event->command);

    expect($commands->contains(fn ($command) => str_contains((string) $command, 'lunar:stock:release-expired')))->toBeTrue();
});
