<?php

declare(strict_types=1);

use Illuminate\Console\OutputStyle;
use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Steps\RectorStep;
use Lunar\Upgrade\Steps\StepContext;
use Lunar\Upgrade\Support\StepReport;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

uses(TestCase::class);

function runRectorStep(): StepReport
{
    $report = new StepReport;

    (new RectorStep)->run(new StepContext(
        dryRun: true,
        paths: ['app'],
        output: new OutputStyle(new ArrayInput([]), new NullOutput),
        report: $report,
    ));

    return $report;
}

it('reports the storefront default context as a manual action', function () {
    $actions = collect(runRectorStep()->manualActions());

    expect($actions->contains(fn (string $action) => str_contains($action, 'StorefrontSession::useAsDefaultContext()')))->toBeTrue();
});

it('reports the cart and order modifier changes as manual actions', function () {
    $actions = collect(runRectorStep()->manualActions());

    expect($actions->contains(fn (string $action) => str_contains($action, 'modifiers now run')))->toBeTrue()
        ->and($actions->contains(fn (string $action) => str_contains($action, 'CalculateLine and CalculateLineSubtotal are removed')))->toBeTrue();
});
