<?php

namespace Lunar\Core\Contracts\Actions\Countries;

use Lunar\Core\Models\State;

interface DeletesCountryState
{
    public function execute(State $state): void;
}
