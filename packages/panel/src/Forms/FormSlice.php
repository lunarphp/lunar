<?php

namespace Lunar\Panel\Forms;

use Lunar\Panel\Contracts\FormSlice as FormSliceContract;

abstract class FormSlice implements FormSliceContract
{
    /**
     * The namespace the panel placed this slice under (`key()` for
     * first-party slices, `addon:{key}` for extensions), bound at
     * registration so field() can build a full key.
     */
    protected ?string $namespace = null;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(array $data): array
    {
        return $data;
    }

    /** @return array<string, string> */
    public function labels(): array
    {
        return [];
    }

    public function bindNamespace(string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * The full form key of one of this slice's own fields, for rule
     * parameters that must reference it (`required_with:` and the like).
     */
    protected function field(string $name): string
    {
        return ($this->namespace ?? $this->key()).':'.$name;
    }
}
