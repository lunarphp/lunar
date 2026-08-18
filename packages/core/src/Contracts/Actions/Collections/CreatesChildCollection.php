<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;

interface CreatesChildCollection
{
    /**
     * @param  string|array<string, string>  $name
     * @param  array<string, mixed>  $attributes  further column values (handle, status, translated descriptions, ...)
     */
    public function execute(Collection $parent, string|array $name, array $attributes = []): Collection;
}
