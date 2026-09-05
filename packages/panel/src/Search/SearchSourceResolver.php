<?php

namespace Lunar\Panel\Search;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;

/**
 * Resolves the registered search sources into ordered, permission-filtered
 * result rows for one term. Owns the matching strategy so a source only
 * declares its query and its LIKE clauses: tokenised LIKE by default, Scout
 * keys when the store has opted in and the source's model is indexed.
 */
class SearchSourceResolver
{
    /** How many tokens a term is split into before the rest are ignored. */
    protected const MAX_TOKENS = 5;

    /** @var list<SearchSource> */
    protected array $sources = [];

    /**
     * @param  array<int, class-string<SearchSource>>  $sourceClasses
     * @param  Authenticatable|null  $user  The panel user visibility checks run against.
     */
    public function __construct(array $sourceClasses, protected ?Authenticatable $user = null)
    {
        foreach ($sourceClasses as $class) {
            $this->sources[] = app($class);
        }
    }

    /**
     * The sources this user may search, in display order.
     *
     * @return list<SearchSource>
     */
    public function visible(): array
    {
        $visible = array_values(array_filter(
            $this->sources,
            fn (SearchSource $source): bool => $source->visible($this->user),
        ));

        return (new OrderResolver)->sort(
            $visible,
            fn (SearchSource $source): string => $source->key(),
            fn (SearchSource $source): Position => $source->position(),
        );
    }

    /**
     * The visible sources as the kind-filter chips the palette renders.
     *
     * @return array<int, array{key: string, label: string, icon: string}>
     */
    public function kinds(): array
    {
        return array_map(fn (SearchSource $source): array => [
            'key' => $source->key(),
            'label' => $source->label(),
            'icon' => $source->icon(),
        ], $this->visible());
    }

    /**
     * Search every visible source, optionally narrowed to a set of keys. Each
     * source contributes at most $perSource rows so no one source crowds out
     * the rest.
     *
     * @param  array<int, string>  $kinds
     * @return list<array{kind: string, kind_label: string, id: int|string, label: string, hint: string|null, url: string, icon: string}>
     */
    public function search(string $term, array $kinds = [], int $perSource = 5): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $results = [];

        foreach ($this->visible() as $source) {
            if ($kinds !== [] && ! in_array($source->key(), $kinds, true)) {
                continue;
            }

            foreach ($this->searchSource($source, $term, $perSource) as $row) {
                $results[] = $row;
            }
        }

        return $results;
    }

    /**
     * The result row for a record the user is looking at, or null when no
     * visible source owns its model. Lets the palette offer recently viewed
     * records in exactly the shape a search returns, including for add-on
     * entities, without every record page assembling one itself.
     *
     * @return array{kind: string, kind_label: string, id: int|string, label: string, hint: string|null, url: string, icon: string}|null
     */
    public function rowFor(Model $record): ?array
    {
        foreach ($this->visible() as $source) {
            $query = $source->query();

            if ($query->getModel()::class !== $record::class) {
                continue;
            }

            // Refetched through the source's own query so row() sees the
            // relations it eager loads, rather than lazy loading them.
            $model = $query->whereKey($record->getKey())->first();

            if (! $model) {
                return null;
            }

            return [
                'kind' => $source->key(),
                'kind_label' => $source->label(),
                'icon' => $source->icon(),
                ...$source->row($model),
            ];
        }

        return null;
    }

    /**
     * @return list<array{kind: string, kind_label: string, id: int|string, label: string, hint: string|null, url: string, icon: string}>
     */
    protected function searchSource(SearchSource $source, string $term, int $perSource): array
    {
        $query = $source->query();

        if ($this->shouldUseScout($query->getModel())) {
            $this->applyScoutConstraint($query, $term, $perSource);
        } else {
            $this->applyTokens($query, $source, $term);
        }

        return $query->limit($perSource)->get()
            ->map(fn (Model $model): array => [
                'kind' => $source->key(),
                'kind_label' => $source->label(),
                'icon' => $source->icon(),
                ...$source->row($model),
            ])
            ->values()
            ->all();
    }

    /**
     * AND the term's tokens, each matched against the source's own clauses, so
     * word order and partial words are forgiven ("friday black" finds "Black
     * Friday"). Misspellings are not — that is what the Scout path is for.
     *
     * @param  Builder<covariant Model>  $query
     */
    protected function applyTokens(Builder $query, SearchSource $source, string $term): void
    {
        foreach ($this->tokenise($term) as $token) {
            $query->where(fn (Builder $query) => $source->applyTerm($query, $token));
        }
    }

    /** @return list<string> */
    protected function tokenise(string $term): array
    {
        $tokens = array_values(array_filter(
            preg_split('/\s+/', $term) ?: [],
            fn (string $token): bool => $token !== '',
        ));

        return array_slice($tokens, 0, self::MAX_TOKENS);
    }

    /**
     * Narrow the query to the Scout-returned ids, in relevance order. Typo
     * tolerance comes from the engine (Meilisearch, Typesense); Scout's
     * database driver matches substrings only.
     *
     * @param  Builder<covariant Model>  $query
     */
    protected function applyScoutConstraint(Builder $query, string $term, int $perSource): void
    {
        $model = $query->getModel();

        $ids = collect($model::search($term)->take($perSource)->keys())
            // A custom indexer may prefix the scout key with the model class.
            ->map(fn ($key) => str_replace($model::class.'::', '', (string) $key))
            ->all();

        $query->whereIn($model->getQualifiedKeyName(), $ids)->orderBySequence($ids);
    }

    /**
     * Scout is opt-in: the store must enable it and the model must actually be
     * indexed, so a mixed setup falls back to LIKE per source rather than
     * returning nothing for an unindexed one.
     */
    protected function shouldUseScout(Model $model): bool
    {
        if (! config('lunar.panel.search.scout_enabled', false)) {
            return false;
        }

        return in_array(Searchable::class, class_uses_recursive($model), true);
    }
}
