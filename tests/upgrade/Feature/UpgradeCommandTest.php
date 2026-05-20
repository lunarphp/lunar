<?php

declare(strict_types=1);

use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Support\SchemaGuard;
use Lunar\Upgrade\Support\VersionGuard;

use function Pest\Laravel\artisan;

uses(TestCase::class);

function stubUpgradeGuards(): void
{
    app()->instance(VersionGuard::class, new class extends VersionGuard
    {
        public function assertLatestV1(): void {}
    });

    app()->instance(SchemaGuard::class, new class(app('db')) extends SchemaGuard
    {
        public function assertV1MigrationsApplied(?string $connection = null): void {}
    });
}

it('aborts when lunarphp/core is not installed', function () {
    artisan('lunar:upgrade', ['--dry-run' => true])
        ->expectsOutputToContain('Lunar v1.x is not installed')
        ->assertExitCode(1);
});

it('aborts when an unknown step is requested', function () {
    stubUpgradeGuards();

    artisan('lunar:upgrade', ['--only' => ['does-not-exist'], '--dry-run' => true])
        ->expectsOutputToContain('Unknown upgrade step')
        ->assertExitCode(1);
});

it('runs the plan when guards pass', function () {
    stubUpgradeGuards();

    artisan('lunar:upgrade', ['--dry-run' => true])
        ->expectsOutputToContain('Upgrade plan:')
        ->assertExitCode(0);
});
