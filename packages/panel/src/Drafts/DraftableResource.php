<?php

namespace Lunar\Panel\Drafts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Contracts\DraftableResource as DraftableResourceContract;

abstract class DraftableResource implements DraftableResourceContract
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(array $data): array
    {
        return $data;
    }

    public function acceptsField(Model $record, string $key): bool
    {
        return in_array($key, $this->fields(), true);
    }

    /** @return array<string, string> */
    public function labels(): array
    {
        return [];
    }
}
