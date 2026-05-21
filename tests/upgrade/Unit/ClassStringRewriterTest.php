<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Support\ClassStringRewriter;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class);

beforeEach(function () {
    Schema::create('upgrade_test_table', function ($table) {
        $table->id();
        $table->string('subject_type');
    });

    DB::table('upgrade_test_table')->insert([
        ['subject_type' => 'Lunar\Core\Models\Product'],
        ['subject_type' => 'Lunar\Core\Models\Product'],
        ['subject_type' => 'Lunar\Core\Models\Order'],
        ['subject_type' => 'App\Models\Other'],
    ]);
});

afterEach(function () {
    Schema::drop('upgrade_test_table');
});

it('rewrites mapped class strings and leaves others alone', function () {
    $rewriter = app(ClassStringRewriter::class);

    $affected = $rewriter->rewrite('upgrade_test_table', 'subject_type', [
        'Lunar\Core\Models\Product' => 'Lunar\Core\Models\Product',
        'Lunar\Core\Models\Order' => 'Lunar\Core\Models\Order',
    ]);

    expect($affected)->toBe(3);

    assertDatabaseCount('upgrade_test_table', 4);
    assertDatabaseHas('upgrade_test_table', ['subject_type' => 'Lunar\Core\Models\Product']);
    assertDatabaseHas('upgrade_test_table', ['subject_type' => 'Lunar\Core\Models\Order']);
    assertDatabaseHas('upgrade_test_table', ['subject_type' => 'App\Models\Other']);
});

it('reports counts in dry-run without writing', function () {
    $rewriter = app(ClassStringRewriter::class);

    $counts = $rewriter->dryRun('upgrade_test_table', 'subject_type', [
        'Lunar\Core\Models\Product' => 'Lunar\Core\Models\Product',
        'Lunar\Core\Models\Missing' => 'Lunar\Core\Models\Missing',
    ]);

    expect($counts)->toBe([
        'Lunar\Core\Models\Product' => 2,
        'Lunar\Core\Models\Missing' => 0,
    ]);

    assertDatabaseHas('upgrade_test_table', ['subject_type' => 'Lunar\Core\Models\Product']);
});
