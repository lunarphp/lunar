<?php

namespace Lunar\Api\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Lunar\Api\Admin\Auth\Abilities;
use Lunar\Api\Models\ApiKey;
use Lunar\Core\Auth\Manifest;
use Lunar\Core\Models\Staff;

class ApiKeyCommand extends Command
{
    protected $signature = 'lunar:api:key
        {action : create, list or revoke}
        {--name= : The key name (create)}
        {--ability=* : An ability to grant, repeatable; defaults to * (create)}
        {--staff= : Email or public id of the owning staff member (create)}
        {--expires= : Days until the key expires (create)}
        {--key= : Public id or token prefix of the key to revoke}';

    protected $description = 'Create, list or revoke admin API keys';

    public function handle(Manifest $manifest): int
    {
        return match ($this->argument('action')) {
            'create' => $this->create($manifest),
            'list' => $this->list(),
            'revoke' => $this->revoke(),
            default => $this->failWith("Unknown action [{$this->argument('action')}]. Use create, list or revoke."),
        };
    }

    protected function create(Manifest $manifest): int
    {
        $name = $this->option('name') ?: $this->ask('Key name');

        if (! $name) {
            return $this->failWith('A key name is required.');
        }

        $abilities = $this->option('ability') ?: [Abilities::ALL];
        $unknown = array_diff($abilities, Abilities::all($manifest));

        if ($unknown !== []) {
            return $this->failWith('Unknown abilities: '.implode(', ', $unknown));
        }

        $staff = null;

        if ($identifier = $this->option('staff')) {
            $staff = Staff::query()->where('email', $identifier)->orWhere('public_id', $identifier)->first();

            if (! $staff) {
                return $this->failWith("No staff member matches [{$identifier}].");
            }
        }

        $expiresAt = $this->option('expires') ? Carbon::now()->addDays((int) $this->option('expires')) : null;

        $issued = ApiKey::generate($name, $abilities, $staff, $expiresAt);

        $this->components->info("Created API key {$issued->key->public_id}.");
        $this->components->twoColumnDetail('Token (shown once)', $issued->plainTextToken);
        $this->components->twoColumnDetail('Abilities', implode(', ', $issued->key->abilities));

        if ($expiresAt) {
            $this->components->twoColumnDetail('Expires', $expiresAt->toIso8601String());
        }

        return self::SUCCESS;
    }

    protected function list(): int
    {
        $rows = ApiKey::query()->with('staff')->latest('id')->get()->map(fn (ApiKey $key) => [
            $key->public_id,
            $key->name,
            $key->token_prefix.'...',
            implode(', ', $key->abilities),
            $key->staff?->email ?? '-',
            $key->last_used_at?->toDateTimeString() ?? '-',
            $key->revoked_at ? 'revoked' : ($key->expires_at?->isPast() ? 'expired' : 'active'),
        ]);

        $this->table(['Id', 'Name', 'Prefix', 'Abilities', 'Owner', 'Last used', 'Status'], $rows->all());

        return self::SUCCESS;
    }

    protected function revoke(): int
    {
        $identifier = $this->option('key') ?: $this->ask('Key public id or token prefix');

        $key = ApiKey::query()->where('public_id', $identifier)->orWhere('token_prefix', $identifier)->first();

        if (! $key) {
            return $this->failWith("No API key matches [{$identifier}].");
        }

        $key->revoke();

        $this->components->info("Revoked API key {$key->public_id} ({$key->name}).");

        return self::SUCCESS;
    }

    protected function failWith(string $message): int
    {
        $this->components->error($message);

        return self::FAILURE;
    }
}
