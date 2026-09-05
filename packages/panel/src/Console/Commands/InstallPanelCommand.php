<?php

namespace Lunar\Panel\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Core\Models\Staff;
use Lunar\Panel\PanelManager;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

class InstallPanelCommand extends Command
{
    protected $signature = 'lunar:panel:install';

    protected $description = 'Publish the Lunar panel config and compiled assets';

    public function handle(PanelManager $manager): int
    {
        info('Installing the Lunar panel...');

        $this->call('vendor:publish', [
            '--tag' => 'panel-config',
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'panel-all-assets',
            '--force' => true,
        ]);

        $this->offerToCreateAdminAccount();

        info('Lunar panel installed. Visit it at: '.url($manager->path()));

        return self::SUCCESS;
    }

    /**
     * Offer to create the first admin staff account.
     *
     * Skipped when one already exists and on any non-interactive run — the
     * command re-runs on every deploy, so it must never block a pipeline.
     * The tty condition mirrors the framework's own prompt-interactivity
     * check, which treats unit tests as interactive so the offer is testable.
     */
    protected function offerToCreateAdminAccount(): void
    {
        $interactive = $this->input->isInteractive()
            && ($this->laravel->runningUnitTests() || (defined('STDIN') && stream_isatty(STDIN)));

        /** @var class-string<Staff> $staffModel */
        $staffModel = config('lunar.staff.model', Staff::class);

        if (! $interactive || $staffModel::whereAdmin(true)->exists()) {
            return;
        }

        if (confirm('No admin staff account exists. Create one now?')) {
            $this->call('lunar:create-admin');
        }
    }
}
