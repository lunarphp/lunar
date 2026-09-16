<?php

namespace Lunar\Panel\Contracts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Models\EditDraft;

/**
 * A namespaced contribution to a draftable resource's edit draft. A slice
 * declares its fields, current values, rules, labels and commit in its own
 * unprefixed terms; the panel composes it into the resource under
 * `{namespace}:{field}` keys the slice never sees. First-party surfaces
 * register through Section::draftSlices() and own a bare namespace; add-ons
 * register through Section::draftExtensions() and land under `addon:{key}`.
 */
interface DraftSlice
{
    /** @return class-string<Model> */
    public function model(): string;

    /**
     * The namespace this slice owns on the model: `[a-z0-9_-]+`, unique per
     * model. `addon` is reserved for the public extension hook.
     */
    public function key(): string;

    /**
     * The unprefixed field names for this record. Row-shaped slices derive
     * the set from data (which channels exist, which currencies are enabled).
     *
     * @return array<int, string>
     */
    public function fields(Model $record): array;

    /**
     * The current, normalised stored value of every field, keyed by
     * unprefixed field name.
     *
     * @return array<string, mixed>
     */
    public function currentValues(Model $record): array;

    /**
     * Normalise incoming values into the shape currentValues() reports so
     * equality comparison holds. Receives only this slice's keys.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(array $data): array;

    /**
     * Validation rules keyed by unprefixed field (`.*` entries included);
     * the composer prefixes the keys. Rule parameters pass through verbatim,
     * so a parameter naming one of this slice's own fields needs the full key.
     *
     * @return array<string, mixed>
     */
    public function rules(Model $record): array;

    /**
     * Persist this slice's values: every field present, current values
     * overlaid with the draft, unprefixed. Runs after the resource's own
     * commit inside the same transaction, and delegates to core actions.
     *
     * @param  array<string, mixed>  $values
     */
    public function commit(Model $record, array $values): void;

    /**
     * Unprefixed field to lang key, for the conflict dialog and validation
     * messages. Fields without an entry fall back to their raw key.
     *
     * @return array<string, string>
     */
    public function labels(): array;

    /**
     * Called when a draft holding this slice's keys is discarded or pruned,
     * for slices that keep state outside the draft's JSON columns.
     */
    public function discard(Model $record, EditDraft $draft): void;
}
