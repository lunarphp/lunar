<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Lunar\Admin\Events\CustomerUserEdited;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;

class UserRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'users';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::user.table.name.label')),
            Tables\Columns\TextColumn::make('email')
                ->label(__('lunarpanel::user.table.email.label')),
        ])->actions([
            Tables\Actions\EditAction::make('edit')
                ->after(
                    fn (Model $record) => CustomerUserEdited::dispatch($record)
                )
                ->form([
                    Group::make([
                        TextInput::make('email')
                            ->label(
                                __('lunarpanel::user.form.email.label')
                            )
                            ->required()
                            ->email()
                            ->columnSpan(2),
                        TextInput::make('password')
                            ->label(
                                __('lunarpanel::user.form.password.label')
                            )
                            ->password()
                            ->minLength(8)
                            ->required(fn ($record) => blank($record))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->currentPassword(false)
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label(
                                __('lunarpanel::user.form.password_confirmation.label')
                            )
                            ->password()
                            ->minLength(8),
                    ])->columns(2),

                ]),
        ]);
    }
}
