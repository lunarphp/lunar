<?php

namespace Lunar\Panel\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-resource draft definition: which field keys a form drafts, how their
 * current database values read (normalised for comparison), and how a
 * resolved value set commits. Registered through Section::draftables().
 */
interface DraftableResource
{
    /** @return class-string<Model> */
    public function model(): string;

    /**
     * The field keys this resource drafts; autosave and commit reject any
     * key outside this set.
     *
     * @return array<int, string>
     */
    public function fields(): array;

    /**
     * The current, normalised database value of every draftable field key.
     *
     * @return array<string, mixed>
     */
    public function currentValues(Model $record): array;

    /**
     * Normalise incoming draft values into the same shape currentValues()
     * reports, so equality comparison is meaningful (e.g. empty string to
     * null for nullable text columns, sorted unique ids for relation keys).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(array $data): array;

    /**
     * Validation rules for a full commit payload (every field key present).
     *
     * @return array<string, mixed>
     */
    public function rules(Model $record): array;

    /**
     * Apply a validated, conflict-free full value set to the record. Always
     * delegates to the core action contracts; the panel never writes model
     * fields directly.
     *
     * @param  array<string, mixed>  $values
     */
    public function commit(Model $record, array $values): void;

    /**
     * Field key to lang key, for conflict-dialog field labels. Keys without
     * an entry fall back to the raw field key.
     *
     * @return array<string, string>
     */
    public function labels(): array;
}
