<?php

namespace Lunar\Database\State;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateWeightUnitToKg
{
    public function prepare()
    {
        //
    }

    public function run(): void
    {
        if (! $this->canRun()) {
            return;
        }

        DB::table($this->table())
            ->whereNull('weight_unit')
            ->orWhere('weight_unit', 'mm')
            ->update(['weight_unit' => 'kg']);
    }

    protected function canRun(): bool
    {
        return Schema::hasTable($this->table())
            && Schema::hasColumn($this->table(), 'weight_unit');
    }

    protected function table(): string
    {
        $prefix = config('lunar.database.table_prefix');

        return $prefix.'product_variants';
    }
}
