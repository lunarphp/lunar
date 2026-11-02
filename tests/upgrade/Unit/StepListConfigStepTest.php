<?php

declare(strict_types=1);

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\File;
use Lunar\Core\Manifests\StepManifest;
use Lunar\Tests\Upgrade\TestCase;
use Lunar\Upgrade\Steps\StepContext;
use Lunar\Upgrade\Steps\StepListConfigStep;
use Lunar\Upgrade\Support\StepReport;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

uses(TestCase::class);

// A private directory: Testbench's config path is shared by parallel workers, which would load these files.
beforeEach(function () {
    $this->configDirectory = sys_get_temp_dir().'/lunar-step-list-config-'.uniqid();

    File::ensureDirectoryExists($this->configDirectory);
});

afterEach(function () {
    File::deleteDirectory($this->configDirectory);
});

function publishConfig(string $file, string $contents): void
{
    File::put(test()->configDirectory."/{$file}.php", "<?php\n\n".$contents);
}

function runStepListConfigStep(): StepReport
{
    $report = new StepReport;

    $step = new class(app(StepManifest::class), test()->configDirectory) extends StepListConfigStep
    {
        public function __construct(StepManifest $manifest, protected string $directory)
        {
            parent::__construct($manifest);
        }

        protected function configDirectory(): string
        {
            return $this->directory;
        }
    };

    $step->run(new StepContext(
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
