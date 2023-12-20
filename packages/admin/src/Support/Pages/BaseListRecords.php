<?php

namespace Lunar\Admin\Support\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

abstract class BaseListRecords extends ListRecords
{
    use Concerns\ExtendsHeaderActions;
    use \Lunar\Admin\Support\Concerns\CallsHooks;

    public function table(Table $table): Table
    {
        parent::table($table);

        return $table
            ->paginated([20, 50, 100]);
    }
}