<?php

namespace Lunar\Filament\Actions\Products\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Contracts\Actions\Products\UpdatesProductStatus;
use Lunar\Core\Facades\DB;

trait AppliesProductStatusInBulk
{
    protected function applyProductStatusInBulk(Collection $records, string $status): void
    {
        DB::beginTransaction();

        foreach ($records as $record) {
            app(UpdatesProductStatus::class)->execute($record, $status);
        }

        DB::commit();
    }
}
