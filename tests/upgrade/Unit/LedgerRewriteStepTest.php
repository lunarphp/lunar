<?php

declare(strict_types=1);

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Steps\LedgerRewriteStep;
use Lunar\Upgrade\Steps\StepContext;
use Lunar\Upgrade\Support\StepReport;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(TestCase::class);

beforeEach(function () {
    Schema::create('migrations', function ($table) {
        $table->id();
        $table->string('migration');
        $table->integer('batch');
    });

    DB::table('migrations')->insert([
        ['migration' => '2021_07_29_100000_create_channels_table', 'batch' => 1],
        ['migration' => '2026_04_30_100000_add_unique_lunar_product_product_option', 'batch' => 4],
        ['migration' => '2030_01_01_000000_external_migration', 'batch' => 5],
    ]);
});

afterEach(function () {
    Schema::drop('migrations');
});

it('removes v1 rows and inserts v2 baseline rows', function () {
    Config::set('lunar.upgrade.ledger', [
        'v1_match' => ['/^2021_/', '/^2026_04_/'],
        'v2_baseline' => [
            '2026_01_01_000000_create_channels_table',
            '2026_01_01_000001_create_languages_table',
        ],
    ]);

    $report = new StepReport;
    $context = new StepContext(
        dryRun: false,
        paths: [],
        output: new OutputStyle(new StringInput(''), new BufferedOutput),
        report: $report,
    );

    app(LedgerRewriteStep::class)->run($context);

    assertDatabaseMissing('migrations', ['migration' => '2021_07_29_100000_create_channels_table']);
    assertDatabaseMissing('migrations', ['migration' => '2026_04_30_100000_add_unique_lunar_product_product_option']);
    assertDatabaseHas('migrations', ['migration' => '2030_01_01_000000_external_migration']);
    assertDatabaseHas('migrations', ['migration' => '2026_01_01_000000_create_channels_table']);
    assertDatabaseHas('migrations', ['migration' => '2026_01_01_000001_create_languages_table']);
});

it('reports counts in dry-run without modifying the ledger', function () {
    Config::set('lunar.upgrade.ledger', [
        'v1_match' => ['/^2021_/'],
        'v2_baseline' => ['2026_01_01_000000_create_channels_table'],
    ]);

    $report = new StepReport;
    $context = new StepContext(
        dryRun: true,
        paths: [],
        output: new OutputStyle(new StringInput(''), new BufferedOutput),
        report: $report,
    );

    app(LedgerRewriteStep::class)->run($context);

    assertDatabaseHas('migrations', ['migration' => '2021_07_29_100000_create_channels_table']);
    assertDatabaseMissing('migrations', ['migration' => '2026_01_01_000000_create_channels_table']);

    expect($report->rows()[0]['status'])->toBe(StepReport::STATUS_DRY_RUN);
});

it('skips when no ledger config is set', function () {
    Config::set('lunar.upgrade.ledger', ['v1_match' => [], 'v2_baseline' => []]);

    $report = new StepReport;
    $context = new StepContext(
        dryRun: false,
        paths: [],
        output: new OutputStyle(new StringInput(''), new BufferedOutput),
        report: $report,
    );

    app(LedgerRewriteStep::class)->run($context);

    expect($report->rows()[0]['status'])->toBe(StepReport::STATUS_SKIPPED);
});
