<?php

namespace Lunar\DemoData\Support;

use Faker\Factory;
use Faker\Generator;

/**
 * Shared state threaded through the generators: the chosen scale, the
 * deterministic faker, and a bag of handles/ids one generator hands to the
 * next (e.g. the default channel, currencies, the default location).
 */
class DemoContext
{
    /** @var array<string, mixed> */
    protected array $handles = [];

    public function __construct(
        public readonly string $scale,
        public readonly bool $fresh,
        public readonly Generator $faker,
        public readonly int $seed = 0,
    ) {
        $this->faker->seed($seed);
    }

    /**
     * Build a context from config defaults — used when the seeder is called
     * directly (e.g. from a host `DatabaseSeeder`) rather than via the command.
     */
    public static function fromConfig(?string $scale = null, bool $fresh = false): self
    {
        return new self(
            scale: $scale ?? (string) config('lunar.demo-data.default_scale', 'small'),
            fresh: $fresh,
            faker: Factory::create(),
            seed: (int) config('lunar.demo-data.faker_seed', 0),
        );
    }

    /**
     * Re-apply the base seed. Generators call this at the start so each is
     * reproducible independently of RNG that earlier generators (and the
     * framework) consume from the global stream mid-seed.
     */
    public function reseed(): void
    {
        $this->faker->seed($this->seed);
    }

    /**
     * The volume knobs for the active scale.
     *
     * @return array<string, int>
     */
    public function counts(): array
    {
        return (array) config("lunar.demo-data.scales.{$this->scale}", []);
    }

    public function count(string $key, int $default = 0): int
    {
        return (int) ($this->counts()[$key] ?? $default);
    }

    public function set(string $key, mixed $value): mixed
    {
        return $this->handles[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->handles[$key] ?? $default;
    }
}
