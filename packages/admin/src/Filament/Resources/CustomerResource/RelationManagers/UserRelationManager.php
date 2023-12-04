<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::user.table.name.label')),
            Tables\Columns\TextColumn::make('email')
                ->label(__('lunarpanel::user.table.email.label')),
        ]);
    }
}
