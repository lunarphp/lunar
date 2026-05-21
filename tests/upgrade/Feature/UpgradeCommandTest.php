<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Support\SchemaGuard;
use Lunar\Upgrade\Support\VersionGuard;

use function Pest\Laravel\artisan;

uses(TestCase::class);

beforeEach(function () {
    Schema::create('migrations', function ($table) {
        $table->id();
        $table->string('migration');
        $table->integer('batch');
    });
});

afterEach(function () {
    Schema::drop('migrations');
});

function stubUpgradeGuards(): void
{
    app()->instance(VersionGuard::class, new class(app('db')) extends VersionGuard
    {
        public function assertV1SchemaPresent(?string $connection = null): void {}
    });

    app()->instance(SchemaGuard::class, new class(app('db')) extends SchemaGuard
    {
        public function assertV1MigrationsApplied(?string $connection = null): void {}
    });
}

it('aborts when no Lunar v1 migration rows are present', function () {
    artisan('lunar:upgrade', ['--dry-run' => true])
        ->expectsOutputToContain('No Lunar v1.x migration rows')
        ->assertExitCode(1);
});

it('aborts when the v2 baseline is already recorded', function () {
    DB::table('migrations')->insert([
        ['migration' => '2021_07_29_100000_create_channels_table', 'batch' => 1],
        ['migration' => '2026_01_01_000000_create_assets_table', 'batch' => 2],
    ]);

    artisan('lunar:upgrade', ['--dry-run' => true])
        ->expectsOutputToContain('v2 baseline migrations are already recorded')
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

    config(['lunar.upgrade.ledger' => ['v1_match' => [], 'v2_baseline' => []]]);

    artisan('lunar:upgrade', ['--dry-run' => true])
        ->expectsOutputToContain('Upgrade plan:')
        ->assertExitCode(0);
});
