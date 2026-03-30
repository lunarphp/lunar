<?php

namespace Lunar\Admin\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Filament\Resources\ActivityResource\Pages\ListActivities;
use Lunar\Admin\Filament\Resources\ActivityResource\Pages\ViewActivity;
use Lunar\Admin\Support\Resources\BaseResource;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends BaseResource
{
    protected static ?string $permission = 'settings';

    protected static ?string $model = Activity::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::activity.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::activity.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::activity');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('causer_type')
                    ->label(__('lunarpanel::activity.form.causer_type'))
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                    ]),
                TextInput::make('causer_id')
                    ->label(__('lunarpanel::activity.form.causer_id'))
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                    ]),
                TextInput::make('subject_type')
                    ->label(__('lunarpanel::activity.form.subject_type'))
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                    ]),
                TextInput::make('subject_id')
                    ->label(__('lunarpanel::activity.form.subject_id'))
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                    ]),
                TextInput::make('description')
                    ->label(__('lunarpanel::activity.form.description'))->columnSpan(2),
                KeyValue::make('properties.attributes')
                    ->label(__('lunarpanel::activity.form.attributes'))
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                    ]),
                KeyValue::make('properties.old')
                    ->label(__('lunarpanel::activity.form.old'))
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                    ]),
            ]);
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('subject_type')
                    ->label(__('lunarpanel::activity.table.subject'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('lunarpanel::activity.table.description'))
                    ->searchable(),
                TextColumn::make('log_name')
                    ->label(__('lunarpanel::activity.table.log')),
                TextColumn::make('created_at')
                    ->label(__('lunarpanel::activity.table.logged_at'))
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label(__('lunarpanel::activity.table.event'))
                    ->multiple()
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('logged_from')
                            ->label(__('lunarpanel::activity.table.logged_from')),
                        DatePicker::make('logged_until')
                            ->label(__('lunarpanel::activity.table.logged_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['logged_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['logged_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['logged_from'] ?? null) {
                            $indicators['logged_from'] = 'Created from '.Carbon::parse($data['logged_from'])->toFormattedDateString();
                        }

                        if ($data['logged_until'] ?? null) {
                            $indicators['logged_until'] = 'Created until '.Carbon::parse($data['logged_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('id', 'DESC');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }
}
