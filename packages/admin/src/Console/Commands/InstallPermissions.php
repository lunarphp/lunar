<?php

namespace Lunar\Hub\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Hub\Actions\Permission\SyncRolesPermissions;

class InstallPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:hub:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync hub roles and permissions';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Sync hub roles and permissions');

        app(SyncRolesPermissions::class);
    }
}
