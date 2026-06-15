<?php

namespace Lunar\Core\Manifests;

use Lunar\Core\Contracts\ReasonManifest as ReasonManifestContract;

abstract class ReasonManifest implements ReasonManifestContract
{
    /**
     * The current key => label reason set.
     *
     * @var array<string, string>
     */
    protected array $reasons;

    public function __construct()
    {
        $this->reasons = $this->defaults();
    }

    /**
     * The code-level default reason set.
     *
     * @return array<string, string>
     */
    abstract protected function defaults(): array;

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->reasons;
    }

    public function label(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        return $this->reasons[$key] ?? $key;
    }

    /**
     * {@inheritDoc}
     */
    public function set(array $reasons): static
    {
        $this->reasons = $reasons;

        return $this;
    }

    public function add(string $key, string $label): static
    {
        $this->reasons[$key] = $label;

        return $this;
    }
}
