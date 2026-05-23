<?php

namespace Lunar\Filament\Actions\Products\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Actions\Products\UpdateProductStatus;
use Lunar\Core\Facades\DB;

trait AppliesProductStatusInBulk
{
    protected function applyProductStatusInBulk(Collection $records, string $status): void
    {
        DB::beginTransaction();

        foreach ($records as $record) {
            UpdateProductStatus::run($record, $status);
        }

        DB::commit();
    }
}
