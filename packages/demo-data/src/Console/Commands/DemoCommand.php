<?php

namespace Lunar\DemoData\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Lunar\DemoData\Database\Seeders\DemoDataSeeder;
use Lunar\DemoData\Support\DemoContext;

class DemoCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'lunar:demo-data
                            {--scale= : Volume of data to generate: small, medium or large}
                            {--fresh : Wipe demo-owned tables before seeding}
                            {--force : Required to run in a production environment}';

    protected $description = 'Seed a realistic demo store (catalogue, customers, orders) for evaluation and review.';

    public function handle(): int
    {
        // Blocks in production unless --force, mirroring migrate:fresh / db:wipe.
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $scale = (string) ($this->option('scale') ?: config('lunar.demo-data.default_scale', 'small'));

        $scales = (array) config('lunar.demo-data.scales', []);

        if (! array_key_exists($scale, $scales)) {
            $this->components->error("Unknown scale [{$scale}]. Use one of: ".implode(', ', array_keys($scales)).'.');

            return self::FAILURE;
        }

        $context = DemoContext::fromConfig($scale, (bool) $this->option('fresh'));

        $this->components->info("Seeding demo data — scale: {$scale}".($context->fresh ? ' (fresh)' : '').'.');

        app(DemoDataSeeder::class)
            ->usingContext($context)
            ->setCommand($this)
            ->run();

        $this->components->info('Demo data seeded.');

        return self::SUCCESS;
    }
}
