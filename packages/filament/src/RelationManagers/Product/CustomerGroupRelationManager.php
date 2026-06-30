<?php

namespace Lunar\Filament\RelationManagers\Product;

use Filament\Actions\AttachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Filament\RelationManagers\BaseRelationManager;

class CustomerGroupRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'customerGroups';

    public ?string $description = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::relationmanagers.customer_groups.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function getPivotColumns(): array
    {
        return collect($this->getRelationship()->getPivotColumns())
            ->reject(
                fn ($column) => in_array($column, ['created_at', 'updated_at', 'deleted_at', 'ends_at', 'starts_at'])
            )->toArray();
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components(
            static::getFormInputs(
                $this->getPivotColumns()
            )
        );
    }

    protected static function getFormInputs(array $pivotColumns = []): array
    {
        $columns = collect($pivotColumns)->map(function ($column) {
            return Toggle::make($column)->label(
                __("lunar-filament::relationmanagers.customer_groups.form.{$column}.label")
            );
        });

        $grid = [];

        if (! $columns->isEmpty()) {
            $grid[] = Grid::make($columns->count())->schema(
                $columns->toArray()
            );
        }

        return [
            ...$grid,
            ...[Grid::make(2)->schema([
                DateTimePicker::make('starts_at')->label(
                    __('lunar-filament::relationmanagers.customer_groups.form.starts_at.label')
                ),
                DateTimePicker::make('ends_at')->label(
                    __('lunar-filament::relationmanagers.customer_groups.form.ends_at.label')
                ),
            ])],
        ];
    }

    public function getDefaultTable(Table $table): Table
    {
        $pivotColumns = collect($this->getPivotColumns())->map(function ($column) {
            return IconColumn::make($column)->label(
                __("lunar-filament::relationmanagers.customer_groups.table.{$column}.label")
            )
                ->color(fn ($state): string => $state ? 'success' : 'warning')
                ->icon(fn ($state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle');
        })->toArray();

        return $table
            ->description(
                $this->description ?: __('lunar-filament::relationmanagers.customer_groups.table.description', [
                    'type' => Str::lower(class_basename(get_class($this->getOwnerRecord()))),
                ])
            )
            ->paginated(false)
            ->headerActions([
                AttachAction::make()->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    ...static::getFormInputs(),
                ])->recordTitle(function ($record) {
                    return $record->name;
                })->preloadRecordSelect()
                    ->label(
                        __('lunar-filament::relationmanagers.customer_groups.actions.attach.label')
                    ),
            ])->columns([
                ...[
                    TextColumn::make('name')
                        ->label(
                            __('lunar-filament::relationmanagers.customer_groups.table.name.label')
                        )
                        ->description(fn ($record) => $record->default
                            ? __('lunar-filament::relationmanagers.customer_groups.table.name.default_description')
                            : null
                        ),
                ],
                ...$pivotColumns,
                ...[
                    TextColumn::make('starts_at')->label(
                        __('lunar-filament::relationmanagers.customer_groups.table.starts_at.label')
                    )->dateTime(),
                    TextColumn::make('ends_at')->label(
                        __('lunar-filament::relationmanagers.customer_groups.table.ends_at.label')
                    )->dateTime(),
                ],
            ])->recordActions([
                EditAction::make(),
            ]);
    }
}
