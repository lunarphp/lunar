<?php

declare(strict_types=1);

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\File;
use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Steps\StepContext;
use Lunar\Upgrade\Steps\StepListConfigStep;
use Lunar\Upgrade\Support\StepReport;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

uses(TestCase::class);

beforeEach(function () {
    File::ensureDirectoryExists(config_path('lunar'));
});

afterEach(function () {
    File::delete([config_path('lunar/cart.php'), config_path('lunar/orders.php'), config_path('lunar/pricing.php')]);
});

function publishConfig(string $file, string $contents): void
{
    File::put(config_path("lunar/{$file}.php"), "<?php\n\n".$contents);
}

function runStepListConfigStep(): StepReport
{
    $report = new StepReport;

    app(StepListConfigStep::class)->run(new StepContext(
        dryRun: true,
        paths: ['app'],
        output: new OutputStyle(new ArrayInput([]), new NullOutput),
        report: $report,
    ));

    return $report;
}

it('skips when no published config defines a step list', function () {
    publishConfig('cart', "return ['auth_policy' => 'merge'];");

    expect(runStepListConfigStep()->rows()[0]['status'])->toBe(StepReport::STATUS_SKIPPED);
});

it('reports v1 default lists as safe to delete', function () {
    publishConfig('cart', <<<'PHP'
        return [
            'pipelines' => [
                'cart_lines' => [
                    Lunar\Pipelines\CartLine\GetUnitPrice::class,
                ],
            ],
        ];
        PHP);

    expect(runStepListConfigStep()->manualActions())
        ->toBe(['These keys match the core defaults; delete them from your config: lunar.cart.pipelines.cart_lines.']);
});

it('reports a customised list with the equivalent Steps calls', function () {
    publishConfig('orders', <<<'PHP'
        return [
            'pipelines' => [
                'creation' => [
                    Lunar\Pipelines\Order\Creation\FillOrderFromCart::class,
                    Lunar\Pipelines\Order\Creation\CreateOrderLines::class,
                    App\Pipelines\TagOrder::class,
                    Lunar\Pipelines\Order\Creation\CreateOrderAddresses::class,
                    Lunar\Pipelines\Order\Creation\CreateShippingLine::class,
                    Lunar\Pipelines\Order\Creation\MapDiscountBreakdown::class,
                ],
            ],
        ];
        PHP);

    expect(runStepListConfigStep()->manualActions())->toBe([
        "Remove [lunar.orders.pipelines.creation] from your config and register the difference in a service provider:\n"
        ."    Steps::for(StepList::OrderCreation)\n"
        ."        ->remove(\\Lunar\\Core\\Pipelines\\Order\\Creation\\CleanUpOrderLines::class)\n"
        .'        ->after(\\Lunar\\Core\\Pipelines\\Order\\Creation\\CreateOrderLines::class, \\App\\Pipelines\\TagOrder::class);',
    ]);
});
