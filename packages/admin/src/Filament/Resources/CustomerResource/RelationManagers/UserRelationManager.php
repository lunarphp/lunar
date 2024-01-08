<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
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
        ])->actions([
            Tables\Actions\EditAction::make('edit')
                ->form([
                    Group::make([
                        TextInput::make('password')
                            ->label(
                                __('lunarpanel::user.form.password.label')
                            )
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->currentPassword(false)

                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label(
                                __('lunarpanel::user.form.password_confirmation.label')
                            )
                            ->required()
                            ->password()
                            ->minLength(8),
                    ])->columns(2),

                ]),
        ]);
    }
}
