<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Models\Collection;

class ManageProductCollections extends ManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'collections';

    protected static ?string $title = 'Collections';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::collections');
    }

    public static function getNavigationLabel(): string
    {
        return 'Collections';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('attribute_data.name')
                    ->formatStateUsing(fn (Collection $record): string => $record->translateAttribute('name'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column, Collection $record): ?string {
                        $state = $column->getState();

                        if (strlen($record->translateAttribute('name')) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $record->translateAttribute('name');
                    })
                    ->description(fn (Collection $record): string => $record->breadcrumb->join(' > '))
                    ->label(__('lunarpanel::product.table.name.label')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelect(
                        function (Forms\Components\Select $select) {
                            return $select->placeholder('Select a collection') // TODO: needs translation
                                ->getSearchResultsUsing(static function (Forms\Components\Select $component, string $search): array {
                                    return Collection::search($search)
                                        ->get()
                                        ->mapWithKeys(fn (Collection $record): array => [$record->getKey() => $record->breadcrumb->push($record->translateAttribute('name'))->join(' > ')])
                                        ->all();
                                });
                        }
                    ),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
