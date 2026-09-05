<?php

namespace Lunar\Core\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Core\Models\Staff;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdmin extends Command
{
    protected $signature = 'lunar:create-admin
                            {--firstname= : The first name of the staff member}
                            {--lastname= : The last name of the staff member}
                            {--email= : A valid and unique email address}
                            {--password= : The password for the staff member}';

    protected $description = 'Create a staff account with full admin rights';

    public function handle(): int
    {
        /** @var class-string<Staff> $model */
        $model = config('lunar.staff.model', Staff::class);

        if (($email = $this->option('email')) !== null && ($error = $this->emailError($email, $model))) {
            $this->components->error($error);

            return self::FAILURE;
        }

        $staff = $model::create([
            'first_name' => $this->option('firstname') ?? text(
                label: 'First Name',
                required: true,
            ),

            'last_name' => $this->option('lastname') ?? text(
                label: 'Last Name',
                required: true,
            ),

            'email' => $email ?? text(
                label: 'Email address',
                required: true,
                validate: fn (string $email): ?string => $this->emailError($email, $model),
            ),

            // The model's `hashed` cast takes care of hashing.
            'password' => $this->option('password') ?? password(
                label: 'Password',
                required: true,
            ),

            'admin' => true,
        ]);

        $this->components->info("Success! {$staff->email} now has admin access.");

        return self::SUCCESS;
    }

    /**
     * @param  class-string<Staff>  $model
     */
    protected function emailError(string $email, string $model): ?string
    {
        return match (true) {
            ! filter_var($email, FILTER_VALIDATE_EMAIL) => 'The email address must be valid.',
            $model::where('email', $email)->exists() => 'A staff member with this email address already exists.',
            default => null,
        };
    }
}
