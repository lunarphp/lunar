<?php

namespace Lunar\Panel\Forms;

use Closure;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\PanelManager;

/**
 * Composes a model's form slices into a plain (non-drafted) form: the
 * create pages and the settings forms. Unlike a drafted page, which holds
 * the whole record's state, a plain form carries only what it posts, so a
 * slice takes part only when a component bound it: its keys join the form
 * on bind, its rules apply when its keys are present, and its commit runs
 * for the namespaces the input holds.
 */
class FormSlices
{
    public function __construct(
        protected PanelManager $panel,
        protected DatabaseManager $db,
    ) {}

    /** @param class-string<Model> $model */
    public function set(string $model): SliceSet
    {
        return new SliceSet($this->panel->formSlicesFor($model));
    }

    /**
     * The prefixed rules of every slice on the model, each under `sometimes`
     * so a namespace no component bound cannot fail validation. Validates
     * against the bound record, or a fresh instance on a create form.
     *
     * @param  class-string<Model>  $model
     * @return array<string, array<int, mixed>>
     */
    public function rules(string $model, ?Model $record = null): array
    {
        $rules = [];

        foreach ($this->set($model)->rules($record ?? new $model) as $key => $fieldRules) {
            $rules[$key] = ['sometimes', ...(is_string($fieldRules) ? explode('|', $fieldRules) : (array) $fieldRules)];
        }

        return $rules;
    }

    /**
     * The prefixed current values of every slice on the record (a fresh
     * instance on a create form), for seeding the page.
     *
     * @return array<string, mixed>
     */
    public function values(Model $record): array
    {
        return $this->set($record::class)->values($record);
    }

    /**
     * Commit the slices whose keys the input holds, each with its current
     * values overlaid by the submitted ones. Call inside the transaction
     * that ran the form's own action; save() does both.
     *
     * @param  array<string, mixed>  $input
     */
    public function commit(Model $record, array $input): void
    {
        $set = $this->set($record::class);

        if ($set->isEmpty()) {
            return;
        }

        foreach ($set->partition($input)['slices'] as $namespace => $values) {
            $slice = $set->slice($namespace);

            $slice->commit($record, [...$slice->currentValues($record), ...$slice->normalize($values)]);
        }
    }

    /**
     * Run a form's action and then commit the input's slices against the
     * record it returns, in one transaction: a failing slice rolls the action
     * back, and an exception from the action reaches the caller untouched.
     *
     * @template TRecord of Model
     *
     * @param  array<string, mixed>  $input
     * @param  Closure(): TRecord  $action
     * @return TRecord
     */
    public function save(array $input, Closure $action): Model
    {
        return $this->db->connection()->transaction(function () use ($input, $action): Model {
            $record = $action();

            $this->commit($record, $input);

            return $record;
        });
    }
}
