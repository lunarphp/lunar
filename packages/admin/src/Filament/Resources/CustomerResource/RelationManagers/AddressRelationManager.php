<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Events\CustomerAddressEdited;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Models\Contracts\Address as AddressContract;
use Lunar\Models\State;

class AddressRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'addresses';

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::address.plural_label');
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->heading(
                __('lunarpanel::address.plural_label')
            )
            ->columns([
                TextColumn::make('title')->label(
                    __('lunarpanel::address.table.title.label')
                ),
                TextColumn::make('first_name')->label(
                    __('lunarpanel::address.table.first_name.label')
                ),
                TextColumn::make('last_name')->label(
                    __('lunarpanel::address.table.last_name.label')
                ),
                TextColumn::make('company_name')->label(
                    __('lunarpanel::address.table.company_name.label')
                ),
                TextColumn::make('tax_identifier')->label(
                    __('lunarpanel::address.table.tax_identifier.label')
                ),
                TextColumn::make('line_one')->label(
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
                TextColumn::make('city')->label(
                    __('lunarpanel::address.table.city.label')
                ),
                TextColumn::make('state')->label(
                    __('lunarpanel::address.table.state.label')
                ),
                TextColumn::make('postcode')->label(
                    __('lunarpanel::address.table.postcode.label')
                ),
                TextColumn::make('contact_email')->label(
                    __('lunarpanel::address.table.contact_email.label')
                ),
                TextColumn::make('contact_phone')->label(
                    __('lunarpanel::address.table.contact_phone.label')
                ),
            ])->recordActions([
                EditAction::make('editAddress')
                    ->after(
                        fn (Model $record) => CustomerAddressEdited::dispatch($record)
                    )
                    ->fillForm(fn (AddressContract $record): array => [
                        'title' => $record->title,
                        'first_name' => $record->first_name,
                        'last_name' => $record->last_name,
                        'company_name' => $record->company_name,
                        'tax_identifier' => $record->tax_identifier,
                        'line_one' => $record->line_one,
                        'line_two' => $record->line_two,
                        'line_three' => $record->line_three,
                        'city' => $record->city,
                        'state' => $record->state,
                        'postcode' => $record->postcode,
                        'contact_email' => $record->contact_email,
                        'contact_phone' => $record->contact_phone,
                    ])
                    ->schema([
                        Group::make()->schema([
                            TextInput::make('title')->label(
                                __('lunarpanel::address.form.title.label')
                            )->columnSpan(1),
                            TextInput::make('first_name')->label(
                                __('lunarpanel::address.form.first_name.label')
                            )->columnSpan(2),
                            TextInput::make('last_name')->label(
                                __('lunarpanel::address.form.last_name.label')
                            )->columnSpan(2),
                        ])->columns(5),
                        TextInput::make('company_name')->label(
                            __('lunarpanel::address.form.company_name.label')
                        ),
                        TextInput::make('tax_identifier')->label(
                            __('lunarpanel::address.form.tax_identifier.label')
                        ),
                        Group::make()->schema([
                            TextInput::make('line_one')->label(
                                __('lunarpanel::address.form.line_one.label')
                            ),
                            TextInput::make('line_two')->label(
                                __('lunarpanel::address.form.line_two.label')
                            ),
                            TextInput::make('line_three')->label(
                                __('lunarpanel::address.form.line_three.label')
                            ),
                        ])->columns(3),
                        Group::make()->schema([
                            Select::make('country_id')->label(
                                __('lunarpanel::address.form.country_id.label')
                            )->relationship(
                                name: 'country',
                            )->getOptionLabelFromRecordUsing(function (Model $record) {
                                $name = $record->native ?: $record->name;

                                return "{$record->emoji} $name";
                            }),
                            TextInput::make('state')->label(
                                __('lunarpanel::address.form.state.label')
                            )->datalist(function ($record) {
                                return State::whereCountryId($record->country_id)
                                    ->where('name', 'LIKE', "%{$record->state}%")
                                    ->get()->map(
                                        fn ($state) => $state->name
                                    );
                            }),
                        ])->columns(2),
                        Group::make()->schema([
                            TextInput::make('city')->label(
                                __('lunarpanel::address.form.city.label')
                            ),
                            TextInput::make('postcode')->label(
                                __('lunarpanel::address.form.postcode.label')
                            ),
                        ])->columns(2),
                        Group::make()->schema([
                            TextInput::make('contact_email')->label(
                                __('lunarpanel::address.form.contact_email.label')
                            ),
                            TextInput::make('contact_phone')->label(
                                __('lunarpanel::address.form.contact_phone.label')
                            ),
                        ])->columns(2),
                    ]),
                DeleteAction::make('deleteAddress'),
            ]);
    }
}
