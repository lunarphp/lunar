<?php

namespace Lunar\Core\Contracts\Actions\Staff;

use Lunar\Core\Models\Staff;

interface CreatesStaff
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Staff;
}
