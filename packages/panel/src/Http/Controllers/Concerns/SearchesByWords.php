<?php

namespace Lunar\Panel\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Word-by-word LIKE matching for list and picker searches. The term is split
 * on whitespace and every word must match one of the caller's columns, so a
 * full name ("Ada Lovelace") finds a record whose first and last name are
 * stored separately, and word order does not matter. A single-word term
 * behaves exactly like a plain LIKE on the whole term.
 */
trait SearchesByWords
{
    /**
     * Constrain the query so every word of the term matches the clauses. The
     * callback receives the query to add OR'd clauses to and the word already
     * wrapped as a LIKE pattern (`%word%`).
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  callable(Builder<TModel>, string): mixed  $clauses
     */
    protected function whereEveryWord(Builder $query, string $term, callable $clauses): void
    {
        foreach (preg_split('/\s+/', trim($term), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
            $query->where(fn (Builder $query) => $clauses($query, "%{$word}%"));
        }
    }
}
