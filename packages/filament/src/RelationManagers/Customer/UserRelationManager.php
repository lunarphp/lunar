<?php

namespace Lunar\Filament\RelationManagers\Customer;

use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Lunar\Filament\Events\CustomerUserEdited;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class UserRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'users';

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::user.plural_label');
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label(__('lunar-filament::user.table.name.label')),
            TextColumn::make('email')
                ->label(__('lunar-filament::user.table.email.label')),
        ])->recordActions([
            EditAction::make('edit')
                ->after(
                    fn (Model $record) => CustomerUserEdited::dispatch($record)
                )
                ->schema([
                    Group::make([
                        TextInput::make('email')
                            ->label(
                                __('lunar-filament::user.form.email.label')
                            )
                            ->required()
                            ->email()
                            ->columnSpan(2),
                        TextInput::make('password')
                            ->label(
                                __('lunar-filament::user.form.password.label')
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
                                __('lunar-filament::user.form.password_confirmation.label')
                            )
                            ->password()
                            ->minLength(8)
                            ->dehydrated(false),
                    ])->columns(2),

                ]),
        ]);
    }
}
