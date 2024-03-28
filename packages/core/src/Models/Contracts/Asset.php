<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Asset
{
    public function file(): MorphOne;
}
