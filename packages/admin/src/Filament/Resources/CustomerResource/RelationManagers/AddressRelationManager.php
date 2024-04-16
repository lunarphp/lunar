<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Address;
use Lunar\Models\State;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(
                __('lunarpanel::address.plural_label')
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')->label(
                    __('lunarpanel::address.table.title.label')
                ),
                Tables\Columns\TextColumn::make('first_name')->label(
                    __('lunarpanel::address.table.first_name.label')
                ),
                Tables\Columns\TextColumn::make('last_name')->label(
                    __('lunarpanel::address.table.last_name.label')
                ),
                Tables\Columns\TextColumn::make('company_name')->label(
                    __('lunarpanel::address.table.company_name.label')
                ),
                Tables\Columns\TextColumn::make('line_one')->label(
                    __('lunarpanel::address.table.line_one.label')
                )->description(function (Model $record) {
                    if (! $record->line_two && $record->line_three) {
                        return $record->line_three;
                    }
                    if (! $record->line_three) {
                        return $record->line_two;
                    }

                    return "{$record->line_two}, {$record->line_three}";
                }),
                Tables\Columns\TextColumn::make('city')->label(
                    __('lunarpanel::address.table.city.label')
                ),
                Tables\Columns\TextColumn::make('state')->label(
                    __('lunarpanel::address.table.state.label')
                ),
                Tables\Columns\TextColumn::make('postcode')->label(
                    __('lunarpanel::address.table.postcode.label')
                ),
                Tables\Columns\TextColumn::make('contact_email')->label(
                    __('lunarpanel::address.table.contact_email.label')
                ),
                Tables\Columns\TextColumn::make('contact_phone')->label(
                    __('lunarpanel::address.table.contact_phone.label')
                ),
            ])->actions([
                Tables\Actions\EditAction::make('editAddress')
                    ->after(
                        fn () => sync_with_search(
                            $this->getOwnerRecord()
                        )
                    )
                    ->fillForm(fn (Address $record): array => [
                        'title' => $record->title,
                        'first_name' => $record->first_name,
                        'last_name' => $record->last_name,
                        'company_name' => $record->company_name,
                        'line_one' => $record->line_one,
                        'line_two' => $record->line_two,
                        'line_three' => $record->line_three,
                        'city' => $record->city,
                        'state' => $record->state,
                        'postcode' => $record->postcode,
                        'contact_email' => $record->contact_email,
                        'contact_phone' => $record->contact_phone,
                    ])
                    ->form([
                        Forms\Components\Group::make()->schema([
                            Forms\Components\TextInput::make('title')->label(
                                __('lunarpanel::address.form.title.label')
                            )->columnSpan(1),
                            Forms\Components\TextInput::make('first_name')->label(
                                __('lunarpanel::address.form.first_name.label')
                            )->columnSpan(2),
                            Forms\Components\TextInput::make('last_name')->label(
                                __('lunarpanel::address.form.last_name.label')
                            )->columnSpan(2),
                        ])->columns(5),
                        Forms\Components\TextInput::make('company_name')->label(
                            __('lunarpanel::address.form.company_name.label')
                        ),
                        Forms\Components\Group::make()->schema([
                            Forms\Components\TextInput::make('line_one')->label(
                                __('lunarpanel::address.form.line_one.label')
                            ),
                            Forms\Components\TextInput::make('line_two')->label(
                                __('lunarpanel::address.form.line_two.label')
                            ),
                            Forms\Components\TextInput::make('line_three')->label(
                                __('lunarpanel::address.form.line_three.label')
                            ),
                        ])->columns(3),
                        Forms\Components\Group::make()->schema([
                            Forms\Components\Select::make('country_id')->label(
                                __('lunarpanel::address.form.country_id.label')
                            )->relationship(
                                name: 'country',
                            )->getOptionLabelFromRecordUsing(function (Model $record) {
                                $name = $record->native ?: $record->name;

                                return "{$record->emoji} $name";
                            }),
                            Forms\Components\TextInput::make('state')->label(
                                __('lunarpanel::address.form.state.label')
                            )->datalist(function ($record) {
                                return State::whereCountryId($record->country_id)
                                    ->where('name', 'LIKE', "%{$record->state}%")
                                    ->get()->map(
                                        fn ($state) => $state->name
                                    );
                            }),
                        ])->columns(2),
                        Forms\Components\Group::make()->schema([
                            Forms\Components\TextInput::make('city')->label(
                                __('lunarpanel::address.form.city.label')
                            ),
                            Forms\Components\TextInput::make('postcode')->label(
                                __('lunarpanel::address.form.postcode.label')
                            ),
                        ])->columns(2),
                        Forms\Components\Group::make()->schema([
                            Forms\Components\TextInput::make('contact_email')->label(
                                __('lunarpanel::address.form.contact_email.label')
                            ),
                            Forms\Components\TextInput::make('contact_phone')->label(
                                __('lunarpanel::address.form.contact_phone.label')
                            ),
                        ])->columns(2),
                    ]),
                Tables\Actions\DeleteAction::make('deleteAddress'),
            ]);
    }
}
