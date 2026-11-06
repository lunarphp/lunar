<?php

namespace Lunar\Panel\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * A namespaced contribution to a first-party form's save. A slice declares
 * its fields, current values, rules, labels and commit in its own unprefixed
 * terms; the panel composes it into the form under `{namespace}:{field}`
 * keys the slice never sees, validates it with the form's own fields, and
 * commits it after them inside the same transaction.
 *
 * This is the whole of the panel's stance on extending first-party forms:
 * an add-on adds fields under a namespace it owns, and can never read, hide
 * or alter the fields the panel owns. The form kind decides the plumbing,
 * not the contract: on a drafted edit page the fields also autosave,
 * restore and conflict-check (see DraftSlice); on a plain form they post
 * with it.
 *
 * First-party surfaces register through Section::formSlices() and own a
 * bare namespace; add-ons register through Section::formExtensions() and
 * land under `addon:{key}`.
 */
interface FormSlice
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
     * On a create form the record is a fresh, unsaved instance.
     *
     * @return array<int, string>
     */
    public function fields(Model $record): array;

    /**
     * The current, normalised stored value of every field, keyed by
     * unprefixed field name. Seeds the form and is the baseline conflict
     * detection compares against on a drafted page.
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
     * Persist this slice's values. The slice persists because it is the only
     * thing that knows where its data lives: the panel never writes an
     * add-on's storage, and a post-save hook outside the transaction would
     * leave ordering and atomicity to chance. Receives every field,
     * unprefixed, current values overlaid with the submitted ones; runs after
     * the form's own commit inside the same transaction, so throwing rolls
     * the record's changes back too. Delegate to core actions.
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
}
