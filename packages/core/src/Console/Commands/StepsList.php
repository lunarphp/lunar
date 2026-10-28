<?php

namespace Lunar\Core\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Core\Contracts\StepManifest;
use Lunar\Core\Enums\StepList;

class StepsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:steps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the steps in each cart, pricing, order and validation step list, in the order they run';

    /**
     * Execute the console command.
     */
    public function handle(StepManifest $steps): int
    {
        foreach (StepList::cases() as $list) {
            $this->components->twoColumnDetail("<fg=green;options=bold>{$list->name}</>", $list->value);

            $resolved = $steps->for($list)->all();

            if ($resolved === []) {
                $this->components->twoColumnDetail('  <fg=gray>(no steps)</>');
            }

            foreach ($resolved as $position => $step) {
                $this->components->twoColumnDetail('  '.($position + 1).'. '.$step);
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }
}
