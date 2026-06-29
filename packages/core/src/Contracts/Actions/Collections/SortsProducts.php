<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Illuminate\Support\Collection;

interface SortsProducts
{
    public function execute(\Lunar\Core\Models\Collection $collection): Collection;
}
