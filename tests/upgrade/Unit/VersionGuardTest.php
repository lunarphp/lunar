<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Exceptions\UpgradeAbortedException;
use Lunar\Upgrade\Support\VersionGuard;

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

it('passes when a v1 canary migration row is present', function () {
    DB::table('migrations')->insert([
        'migration' => VersionGuard::V1_CANARIES[0],
        'batch' => 1,
    ]);

    app(VersionGuard::class)->assertV1SchemaPresent();
})->throwsNoExceptions();

it('aborts when the ledger is empty', function () {
    app(VersionGuard::class)->assertV1SchemaPresent();
})->throws(UpgradeAbortedException::class, 'No Lunar v1.x migration rows');

it('aborts when only non-Lunar migration rows are present', function () {
    DB::table('migrations')->insert([
        'migration' => '2024_05_01_000000_create_app_widgets_table',
        'batch' => 1,
    ]);

    app(VersionGuard::class)->assertV1SchemaPresent();
})->throws(UpgradeAbortedException::class, 'No Lunar v1.x migration rows');

it('aborts when the v2 baseline is already recorded', function () {
    DB::table('migrations')->insert([
        ['migration' => VersionGuard::V1_CANARIES[0], 'batch' => 1],
        ['migration' => '2026_01_01_000000_create_assets_table', 'batch' => 2],
    ]);

    app(VersionGuard::class)->assertV1SchemaPresent();
})->throws(UpgradeAbortedException::class, 'v2 baseline migrations are already recorded');
