<?php

namespace Lunar\Filament\RelationManagers\Customer;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Address;
use Lunar\Filament\Forms\Components\CountrySelect;
use Lunar\Filament\Forms\Components\StateSelect;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class AddressRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'addresses';

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::address.plural_label');
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->heading(
                __('lunar-filament::address.plural_label')
            )
            ->columns([
                TextColumn::make('title')->label(
                    __('lunar-filament::address.table.title.label')
                ),
                TextColumn::make('first_name')->label(
                    __('lunar-filament::address.table.first_name.label')
                ),
                TextColumn::make('last_name')->label(
                    __('lunar-filament::address.table.last_name.label')
                ),
                TextColumn::make('company_name')->label(
                    __('lunar-filament::address.table.company_name.label')
                ),
                TextColumn::make('tax_identifier')->label(
                    __('lunar-filament::address.table.tax_identifier.label')
                ),
                TextColumn::make('line_one')->label(
                    __('lunar-filament::address.table.line_one.label')
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
                    __('lunar-filament::address.table.city.label')
                ),
                TextColumn::make('state')->label(
                    __('lunar-filament::address.table.state.label')
                ),
                TextColumn::make('postcode')->label(
                    __('lunar-filament::address.table.postcode.label')
                ),
                TextColumn::make('contact_email')->label(
                    __('lunar-filament::address.table.contact_email.label')
                ),
                TextColumn::make('contact_phone')->label(
                    __('lunar-filament::address.table.contact_phone.label')
                ),
            ])->recordActions([
                EditAction::make('editAddress')
                    ->fillForm(fn (Address $record): array => [
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
                                __('lunar-filament::address.form.title.label')
                            )->columnSpan(1),
                            TextInput::make('first_name')->label(
                                __('lunar-filament::address.form.first_name.label')
                            )->columnSpan(2),
                            TextInput::make('last_name')->label(
                                __('lunar-filament::address.form.last_name.label')
                            )->columnSpan(2),
                        ])->columns(5),
                        TextInput::make('company_name')->label(
                            __('lunar-filament::address.form.company_name.label')
                        ),
                        TextInput::make('tax_identifier')->label(
                            __('lunar-filament::address.form.tax_identifier.label')
                        ),
                        Group::make()->schema([
                            TextInput::make('line_one')->label(
                                __('lunar-filament::address.form.line_one.label')
                            ),
                            TextInput::make('line_two')->label(
                                __('lunar-filament::address.form.line_two.label')
                            ),
                            TextInput::make('line_three')->label(
                                __('lunar-filament::address.form.line_three.label')
                            ),
                        ])->columns(3),
                        Group::make()->schema([
                            CountrySelect::make('country_id')->label(
                                __('lunar-filament::address.form.country_id.label')
                            ),
                            StateSelect::make('state')->label(
                                __('lunar-filament::address.form.state.label')
                            )->dependsOn('country_id'),
                        ])->columns(2),
                        Group::make()->schema([
                            TextInput::make('city')->label(
                                __('lunar-filament::address.form.city.label')
                            ),
                            TextInput::make('postcode')->label(
                                __('lunar-filament::address.form.postcode.label')
                            ),
                        ])->columns(2),
                        Group::make()->schema([
                            TextInput::make('contact_email')->label(
                                __('lunar-filament::address.form.contact_email.label')
                            ),
                            TextInput::make('contact_phone')->label(
                                __('lunar-filament::address.form.contact_phone.label')
                            ),
                        ])->columns(2),
                    ]),
                DeleteAction::make('deleteAddress'),
            ]);
    }
}
