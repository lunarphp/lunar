<?php

namespace Lunar\Hub\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Lunar\Hub\Models\Staff;

class InstallHub extends Command
{
    protected $commands = [
        'vendor:publish --tag=lunar.hub.public --force',
        'vendor:publish --tag=lunar.livewiretables.public --force',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:hub:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install hub dependancies';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Publishing assets to public folder.');

        // Publish hub public assets
        foreach ($this->commands as $command) {
            Artisan::call($command);
        }

        if (! Staff::whereAdmin(true)->exists()) {
            $this->info('Create an admin user');

            $firstname = $this->ask('Whats your first name?');
            $lastname = $this->ask('Whats your last name?');
            $email = $this->ask('Whats your email address?');
            $password = $this->secret('Enter a password');

            Staff::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'password' => bcrypt($password),
                'admin' => true,
            ]);
        }
    }
}
