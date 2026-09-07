<?php

namespace Lunar\Api\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\Api\Models\ApiKey;
use Lunar\Core\Models\Staff;

class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        $plainText = Str::random(ApiKey::TOKEN_LENGTH);

        return [
            'public_id' => (string) Str::ulid(),
            'name' => $this->faker->words(2, true),
            'token_prefix' => substr($plainText, 0, 8),
            'token_hash' => ApiKey::hashToken($plainText),
            'abilities' => ['*'],
            'staff_id' => null,
        ];
    }

    public function ownedBy(Staff $staff): static
    {
        return $this->state(['staff_id' => $staff->id]);
    }

    /** @param  array<int, string>  $abilities */
    public function abilities(array $abilities): static
    {
        return $this->state(['abilities' => $abilities]);
    }

    public function revoked(): static
    {
        return $this->state(['revoked_at' => now()->subMinute()]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subMinute()]);
    }
}
