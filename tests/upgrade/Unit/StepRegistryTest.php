<?php

declare(strict_types=1);

use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Exceptions\UpgradeAbortedException;
use Lunar\Upgrade\Steps\ComposerRequireRewriteStep;
use Lunar\Upgrade\Steps\DataMigrationStep;
use Lunar\Upgrade\Steps\LedgerRewriteStep;
use Lunar\Upgrade\Steps\RectorStep;
use Lunar\Upgrade\Steps\StepRegistry;
use Lunar\Upgrade\Steps\UpgradeStep;

uses(TestCase::class);

it('resolves every step in declared order', function () {
    $registry = app(StepRegistry::class);

    $steps = $registry->all();

    expect($steps)->toHaveCount(4)
        ->and($steps[0])->toBeInstanceOf(ComposerRequireRewriteStep::class)
        ->and($steps[1])->toBeInstanceOf(RectorStep::class)
        ->and($steps[2])->toBeInstanceOf(DataMigrationStep::class)
        ->and($steps[3])->toBeInstanceOf(LedgerRewriteStep::class);
});

it('filters by only and skip', function () {
    $registry = app(StepRegistry::class);

    $steps = $registry->resolve(only: ['rector']);

    expect($steps)->toHaveCount(1)
        ->and($steps[0]->name())->toBe('rector');

    $steps = $registry->resolve(skip: ['rector']);

    expect(array_map(fn (UpgradeStep $s) => $s->name(), $steps))
        ->toBe(['composer-require-rewrite', 'data-migrations', 'ledger-rewrite']);
});

it('aborts on an unknown step name', function () {
    app(StepRegistry::class)->resolve(only: ['does-not-exist']);
})->throws(UpgradeAbortedException::class, 'Unknown upgrade step: does-not-exist');
