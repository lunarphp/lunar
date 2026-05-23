<?php

namespace Lunar\Filament\Forms\Components\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use Lunar\Filament\Forms\Components\Support\RecordSearch;

/**
 * Wires a Lunar selector up to {@see RecordSearch} and lets the option
 * query be modified per call-site via {@see modifyOptionsQueryUsing()}.
 *
 * Expects the implementing class to expose:
 *  - `model(): class-string<Model>` — the model the selector resolves to.
 *  - `optionsLimit(): int` — how many results to return per search.
 *  - `optionLabel(Model $record): string` — the display label per option.
 */
trait SearchesLunarRecords
{
    /**
     * @var array<int, Closure>
     */
    protected array $optionsQueryModifiers = [];

    /**
     * @var array<int, Closure>
     */
    protected array $optionsCollectionFilters = [];

    /**
     * Attach a closure that receives the search query and returns a
     * modified version. Stackable — multiple closures are applied in
     * registration order.
     */
    public function modifyOptionsQueryUsing(Closure $callback): static
    {
        $this->optionsQueryModifiers[] = $callback;

        return $this;
    }

    /**
     * Attach a closure that filters the resolved results collection
     * after the query has run. Stackable — multiple closures are applied
     * in registration order.
     */
    public function filterOptionsUsing(Closure $callback): static
    {
        $this->optionsCollectionFilters[] = $callback;

        return $this;
    }

    /**
     * @return array<int|string, string>
     */
    protected function searchLunarRecords(string $search): array
    {
        $model = $this->resolveLunarModel();

        $query = RecordSearch::for($model, $search);

        foreach ($this->optionsQueryModifiers as $modifier) {
            $modified = $this->evaluate($modifier, ['query' => $query]);

            if ($modified instanceof Builder || $modified instanceof ScoutBuilder) {
                $query = $modified;
            }
        }

        $results = $query->take($this->resolveOptionsLimit())->get();

        foreach ($this->optionsCollectionFilters as $filter) {
            $results = $results->reject(
                fn (Model $record) => $this->evaluate($filter, ['record' => $record]) === true,
            );
        }

        return $results
            ->mapWithKeys(fn (Model $record): array => [
                $record->getKey() => $this->resolveLunarOptionLabel($record),
            ])
            ->all();
    }

    protected function resolveLunarOptionLabel(Model $record): string
    {
        return method_exists($this, 'optionLabel')
            ? $this->optionLabel($record)
            : ($record->name ?? (string) $record->getKey());
    }

    /**
     * @return class-string<Model>
     */
    protected function resolveLunarModel(): string
    {
        return method_exists($this, 'lunarModel')
            ? $this->lunarModel()
            : throw new \LogicException(static::class.' must implement lunarModel() to use the SearchesLunarRecords trait.');
    }

    protected function resolveOptionsLimit(): int
    {
        if (property_exists($this, 'optionsLimit')) {
            $limit = $this->evaluate($this->optionsLimit);

            if (is_int($limit)) {
                return $limit;
            }
        }

        return 50;
    }
}
