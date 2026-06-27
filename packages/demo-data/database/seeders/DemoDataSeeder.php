<?php

namespace Lunar\DemoData\Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\DemoData\Generators\FoundationGenerator;
use Lunar\DemoData\Generators\Generator;
use Lunar\DemoData\Support\DemoContext;

class DemoDataSeeder extends Seeder
{
    protected ?DemoContext $context = null;

    /**
     * Generators run in dependency order. Populated as each domain lands
     * (foundation, catalogue, customers, orders).
     *
     * @var array<class-string<Generator>>
     */
    protected array $generators = [
        FoundationGenerator::class,
    ];

    public function usingContext(DemoContext $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function run(): void
    {
        $context = $this->context ?? DemoContext::fromConfig();

        foreach ($this->generators as $generator) {
            $this->command?->getOutput()->writeln("  <fg=gray>-</> {$generator}");

            app($generator)->generate($context);
        }
    }
}
