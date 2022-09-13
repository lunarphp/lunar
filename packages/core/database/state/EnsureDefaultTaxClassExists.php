<?php

namespace Lunar\Database\State;

use Lunar\Models\TaxClass;
use Illuminate\Support\Facades\Schema;

class EnsureDefaultTaxClassExists
{
    public function run()
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        // Get the first tax class and make it default
        $taxClass = TaxClass::first();

        if ($taxClass) {
            $taxClass->update([
                'default' => true,
            ]);
        }
    }

    protected function canRun()
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasTable("{$prefix}tax_classes");
    }

    protected function shouldRun()
    {
        return ! TaxClass::whereDefault(true)->count();
    }
}
