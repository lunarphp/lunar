<?php

namespace Lunar\Admin\Console\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Lunar\Admin\Models\Staff;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class MakeLunarAdminCommand extends Command
{
    protected $description = 'Create a new Lunar admin';

    protected $signature = 'lunar:create-admin
                            {--firstname= : The first name of the user}
                            {--lastname= : The last name of the user}
                            {--email= : A valid and unique email address}
                            {--password= : The password for the user (min. 8 characters)}';

    /**
     * @var array{'name': string | null, 'email': string | null, 'password': string | null}
     */
    protected array $options;

    /**
     * @return array{'name': string, 'email': string, 'password': string}
     */
    protected function getUserData(): array
    {
        return [
            'firstname' => $this->options['firstname'] ?? text(
                label: 'First Name',
                required: true,
            ),

            'lastname' => $this->options['lastname'] ?? text(
                label: 'Last Name',
                required: true,
            ),

            'email' => $this->options['email'] ?? text(
                label: 'Email address',
                required: true,
                validate: fn (string $email): ?string => match (true) {
                    ! filter_var($email, FILTER_VALIDATE_EMAIL) => 'The email address must be valid.',
                    Staff::where('email', $email)->exists() => 'A user with this email address already exists',
                    default => null,
                },
            ),

            'password' => Hash::make($this->options['password'] ?? password(
                label: 'Password',
                required: true,
            )),

            'admin' => true,
        ];
    }

    protected function createUser(): Authenticatable
    {
        return Staff::create($this->getUserData());
    }

    protected function sendSuccessMessage(Authenticatable $user): void
    {
        $loginUrl = Filament::getLoginUrl();

        $this->components->info('Success! '.($user->getAttribute('email') ?? 'You')." may now log in at {$loginUrl}");
    }

    public function handle(): void
    {
        $this->options = $this->options();

        $user = $this->createUser();
        $this->sendSuccessMessage($user);
    }
}
