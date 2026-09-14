<?php

namespace Lunar\SearchRelevance\Panel\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Support\Position;
use Lunar\SearchRelevance\Models\SearchQuery;
use Lunar\SearchRelevance\Panel\SearchRelevanceSection;

/** Finds logged normalised queries so staff can jump to a query page from the palette. */
class QuerySearchSource extends SearchSource
{
    public function key(): string
    {
        return 'search-queries';
    }

    public function label(): string
    {
        return __('search-relevance::panel.search_source');
    }

    public function icon(): string
    {
        return 'search';
    }

    public function permission(): string
    {
        return SearchRelevanceSection::PERMISSION;
    }

    public function position(): Position
    {
        return Position::last();
    }

    /**
     * One row per normalised query; the resolver's limit applies to the groups.
     *
     * @return Builder<SearchQuery>
     */
    public function query(): Builder
    {
        return SearchQuery::query()
            ->selectRaw('min(id) as id, normalised_query, count(*) as searches')
            ->groupBy('normalised_query')
            ->orderByDesc('searches')
            ->orderBy('normalised_query');
    }

    public function applyTerm(Builder $query, string $token): void
    {
        $query->where('normalised_query', 'like', "%{$token}%");
    }

    /** @param SearchQuery $model */
    public function row(Model $model): array
    {
        $query = (string) $model->normalised_query;

        return [
            'id' => $query,
            'label' => $query,
            'hint' => __('search-relevance::panel.search_source_hint', ['count' => (int) $model->searches]),
            'url' => route('panel.search-relevance.query', ['query' => $query]),
        ];
    }
}
