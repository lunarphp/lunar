<?php

namespace Lunar\Panel\Drafts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Contracts\DraftSlice as DraftSliceContract;
use Lunar\Panel\Models\EditDraft;

abstract class DraftSlice implements DraftSliceContract
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

    public function discard(Model $record, EditDraft $draft): void {}

    public function bindNamespace(string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * The full draft key of one of this slice's own fields, for rule
     * parameters that must reference it (`required_with:` and the like).
     */
    protected function field(string $name): string
    {
        return ($this->namespace ?? $this->key()).':'.$name;
    }
}
