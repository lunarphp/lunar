<?php

namespace Lunar\Panel\Drafts;

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

    /** @return array<string, string> */
    public function labels(): array
    {
        return [];
    }
}
