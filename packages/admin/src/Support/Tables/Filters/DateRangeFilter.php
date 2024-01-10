<?php

namespace Lunar\Admin\Support\Tables\Filters;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter extends Filter
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->form([
            Group::make([
                DatePicker::make('date_from'),
                DatePicker::make('date_until'),
            ]),
        ]);

        $column = $this->getName();

        $this->query(function (Builder $query, array $data) use ($column): Builder {
            return $query
                ->when(
                    $data['date_from'],
                    fn (Builder $query, $date): Builder => $query->whereDate($column, '>=', $date),
                )
                ->when(
                    $data['date_until'],
                    fn (Builder $query, $date): Builder => $query->whereDate($column, '<=', $date),
                );
        });

        $this->indicateUsing(function (array $data) use ($column): ?string {

            $from = $data['date_from'] ?? null;
            $until = $data['date_until'] ?? null;
            $formattedFrom = $from ? now()->parse($from)->toFormattedDateString() : null;
            $formattedUntil = $until ? now()->parse($until)->toFormattedDateString() : null;

            if (! $from && ! $until) {
                return null;
            }

            if ($from && ! $until) {
                return __(
                    'lunarpanel::filters.date_range.indicator.singular_from',
                    ['date' => $formattedFrom, 'column' => $column]
                );
            }

            if ($until && ! $from) {
                return __(
                    'lunarpanel::filters.date_range.indicator.singular_to',
                    ['date' => $formattedUntil, 'column' => $column]
                );
            }

            return __(
                'lunarpanel::filters.date_range.indicator.range',
                ['from' => $formattedFrom, 'until' => $formattedUntil, 'column' => $column]
            );
        });
    }
}
