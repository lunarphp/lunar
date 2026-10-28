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

it('reports the storefront default context as a manual action', function () {
    $report = new StepReport;

    (new RectorStep)->run(new StepContext(
        dryRun: true,
        paths: ['app'],
        output: new OutputStyle(new ArrayInput([]), new NullOutput),
        report: $report,
    ));

    expect($report->manualActions())->toHaveCount(1)
        ->and($report->manualActions()[0])->toContain('StorefrontSession::useAsDefaultContext()');
});
