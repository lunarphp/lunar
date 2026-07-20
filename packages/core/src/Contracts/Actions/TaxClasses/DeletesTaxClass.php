<?php

namespace Lunar\Core\Contracts\Actions\TaxClasses;

use Lunar\Core\Models\TaxClass;

interface DeletesTaxClass
{
    public function execute(TaxClass $taxClass): void;
}
