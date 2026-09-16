<?php

namespace Lunar\Panel\Drafts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Contracts\DraftSlice;
use Lunar\Panel\Contracts\FormSlice;
use Lunar\Panel\Forms\SliceSet;
use Lunar\Panel\Models\EditDraft;

/**
 * A draftable resource assembled from its own definition plus every form
 * slice registered for its model. Prefixing lives in SliceSet, shared with
 * the plain-form composer: slices deal in their own field names, the manager
 * sees one flat `{namespace}:{field}` key space, and neither can reach the
 * other's keys.
 */
class ComposedDraftResource implements DraftableResource
{
    protected SliceSet $set;

    /**
     * @param  array<string, FormSlice>  $slices  keyed by namespace, in registration order
     */
    public function __construct(
        protected DraftableResource $resource,
        array $slices,
        protected Model $record,
    ) {
        $this->set = new SliceSet($slices);
    }

    public function resource(): DraftableResource
    {
        return $this->resource;
    }

    /** @return array<string, FormSlice> */
    public function slices(): array
    {
        return $this->set->slices();
    }

    public function model(): string
    {
        return $this->resource->model();
    }

    public function fields(): array
    {
        return [...$this->resource->fields(), ...$this->set->fields($this->record)];
    }

    public function currentValues(Model $record): array
    {
        return [...$this->resource->currentValues($record), ...$this->sliceValues($record)];
    }

    /**
     * The prefixed current values of every slice, without the resource's
     * own: what an edit page seeds its form with.
     *
     * @return array<string, mixed>
     */
    public function sliceValues(Model $record): array
    {
        return $this->set->values($record);
    }

    public function normalize(array $data): array
    {
        $partitioned = $this->set->partition($data);

        return [
            ...$this->resource->normalize($partitioned['rest']),
            ...$this->set->normalize(array_diff_key($data, $partitioned['rest'])),
        ];
    }

    public function rules(Model $record): array
    {
        return [...$this->resource->rules($record), ...$this->set->rules($record)];
    }

    /**
     * The resource commits first with its own keys, then each slice with its
     * unprefixed values in registration order. DraftManager wraps the whole
     * call in one transaction, so a failing slice rolls the resource back.
     */
    public function commit(Model $record, array $values): void
    {
        $partitioned = $this->set->partition($values);

        $this->resource->commit($record, $partitioned['rest']);

        foreach ($this->set->slices() as $namespace => $slice) {
            $slice->commit($record, $partitioned['slices'][$namespace] ?? []);
        }
    }

    public function labels(): array
    {
        return [...$this->resource->labels(), ...$this->set->labels()];
    }

    /**
     * Fan a discarded or pruned draft out to the draft-aware slices whose
     * keys it held.
     */
    public function discard(Model $record, EditDraft $draft): void
    {
        foreach ($this->set->partition($draft->data ?? [])['slices'] as $namespace => $values) {
            $slice = $this->set->slice($namespace);

            if ($slice instanceof DraftSlice) {
                $slice->discard($record, $draft);
            }
        }
    }
}
