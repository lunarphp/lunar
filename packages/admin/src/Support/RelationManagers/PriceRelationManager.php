<?php

namespace Lunar\Admin\Support\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;

class PriceRelationManager extends RelationManager
{
    protected static string $relationship = 'basePrices';
}
