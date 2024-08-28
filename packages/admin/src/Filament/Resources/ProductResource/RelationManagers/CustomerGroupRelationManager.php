<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\RelationManagers;

use Filament;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Lunar\Admin\Events\ProductCustomerGroupsUpdated;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;

class CustomerGroupRelationManager extends BaseRelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'customerGroups';

    public ?string $description = null;

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

    public function getDefaultForm(Form $form): Form
    {
        return $form->schema(
            static::getFormInputs(
                $this->getPivotColumns()
            )
        );
    }

    protected static function getFormInputs(array $pivotColumns = []): array
    {
        $columns = collect($pivotColumns)->map(function ($column) {
            return Filament\Forms\Components\Toggle::make($column)->label(
                __("lunarpanel::relationmanagers.customer_groups.form.{$column}.label")
            );
        });

        $grid = [];

        if (! $columns->isEmpty()) {
            $grid[] = Filament\Forms\Components\Grid::make($columns->count())->schema(
                $columns->toArray()
            );
        }

        return [
            ...$grid,
            ...[Filament\Forms\Components\Grid::make(2)->schema([
                Filament\Forms\Components\DateTimePicker::make('starts_at')->label(
                    __('lunarpanel::relationmanagers.customer_groups.form.starts_at.label')
                ),
                Filament\Forms\Components\DateTimePicker::make('ends_at')->label(
                    __('lunarpanel::relationmanagers.customer_groups.form.ends_at.label')
                ),
            ])],
        ];
    }

    public function getDefaultTable(Table $table): Table
    {
        $pivotColumns = collect($this->getPivotColumns())->map(function ($column) {
            return Tables\Columns\IconColumn::make($column)->label(
                __("lunarpanel::relationmanagers.customer_groups.table.{$column}.label")
            )
                ->color(fn (string $state): string => match ($state) {
                    '1' => 'success',
                    '0' => 'warning',
                })->icon(fn (string $state): string => match ($state) {
                    '0' => 'heroicon-o-x-circle',
                    '1' => 'heroicon-o-check-circle',
                });
        })->toArray();

        return $table
            ->description(
                $this->description ?: __('lunarpanel::relationmanagers.customer_groups.table.description', [
                    'type' => Str::lower(class_basename(get_class($this->getOwnerRecord())))
                ])
            )
            ->paginated(false)
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(fn (Tables\Actions\AttachAction $action): array => [
                    $action->getRecordSelect(),
                    ...static::getFormInputs(),
                ])->recordTitle(function ($record) {
                    return $record->name;
                })->preloadRecordSelect()
                    ->label(
                        __('lunarpanel::relationmanagers.customer_groups.actions.attach.label')
                    )->after(
                        fn () => ProductCustomerGroupsUpdated::dispatch($this->getOwnerRecord())
                    ),
            ])->columns([
                ...[
                    Tables\Columns\TextColumn::make('name')->label(
                        __('lunarpanel::relationmanagers.customer_groups.table.name.label')
                    ),
                ],
                ...$pivotColumns,
                ...[
                    Tables\Columns\TextColumn::make('starts_at')->label(
                        __('lunarpanel::relationmanagers.customer_groups.table.starts_at.label')
                    )->dateTime(),
                    Tables\Columns\TextColumn::make('ends_at')->label(
                        __('lunarpanel::relationmanagers.customer_groups.table.ends_at.label')
                    )->dateTime(),
                ],
            ])->actions([
                Tables\Actions\EditAction::make()->after(
                    fn () => ProductCustomerGroupsUpdated::dispatch(
                        $this->getOwnerRecord()
                    )
                ),
            ]);
    }
}
