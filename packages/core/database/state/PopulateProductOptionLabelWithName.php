<?php

namespace Lunar\Database\State;

use Illuminate\Support\Facades\Schema;
use Lunar\Facades\DB;
use Lunar\Models\ProductOption;

class PopulateProductOptionLabelWithName
{
    public function prepare()
    {
        //
    }

    public function run()
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        DB::transaction(function () {
            ProductOption::where('label', '')
                ->orWhereNull('label')
                ->update([
                    'label' => DB::raw('name'),
                ]);
        });
    }

    protected function canRun()
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasTable("{$prefix}product_options");
    }

    protected function shouldRun()
    {
        return ProductOption::whereJsonLength('label', 0)->count() > 0;
    }
}
