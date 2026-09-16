<?php

namespace Lunar\Panel\Drafts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Contracts\DraftSlice;
use Lunar\Panel\Models\EditDraft;

/**
 * A draftable resource assembled from its own definition plus every draft
 * slice registered for its model. The only place slice keys are prefixed
 * and unprefixed: slices deal in their own field names, the manager sees
 * one flat `{namespace}:{field}` key space, and neither can reach the
 * other's keys.
 */
class ComposedDraftResource implements DraftableResource
{
    /**
     * @param  array<string, DraftSlice>  $slices  keyed by namespace, in registration order
     */
    public function __construct(
        protected DraftableResource $resource,
        protected array $slices,
        protected Model $record,
    ) {}

    public function resource(): DraftableResource
    {
        return $this->resource;
    }

    /** @return array<string, DraftSlice> */
    public function slices(): array
    {
        return $this->slices;
    }

    public function model(): string
    {
        return $this->resource->model();
    }

    public function fields(): array
    {
        $fields = $this->resource->fields();

        foreach ($this->slices as $namespace => $slice) {
            foreach ($slice->fields($this->record) as $field) {
                $fields[] = $this->prefix($namespace, $field);
            }
        }

        return $fields;
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
        $values = [];

        foreach ($this->slices as $namespace => $slice) {
            foreach ($slice->currentValues($record) as $field => $value) {
                $values[$this->prefix($namespace, $field)] = $value;
            }
        }

        return $values;
    }

    public function normalize(array $data): array
    {
        $partitioned = $this->partition($data);

        $normalized = $this->resource->normalize($partitioned['resource']);

        foreach ($partitioned['slices'] as $namespace => $values) {
            foreach ($this->slices[$namespace]->normalize($values) as $field => $value) {
                $normalized[$this->prefix($namespace, $field)] = $value;
            }
        }

        return $normalized;
    }

    public function rules(Model $record): array
    {
        $rules = $this->resource->rules($record);

        foreach ($this->slices as $namespace => $slice) {
            foreach ($slice->rules($record) as $field => $fieldRules) {
                $rules[$this->prefix($namespace, $field)] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * The resource commits first with its own keys, then each slice with its
     * unprefixed values in registration order. DraftManager wraps the whole
     * call in one transaction, so a failing slice rolls the resource back.
     */
    public function commit(Model $record, array $values): void
    {
        $partitioned = $this->partition($values);

        $this->resource->commit($record, $partitioned['resource']);

        foreach ($this->slices as $namespace => $slice) {
            $slice->commit($record, $partitioned['slices'][$namespace] ?? []);
        }
    }

    public function labels(): array
    {
        $labels = $this->resource->labels();

        foreach ($this->slices as $namespace => $slice) {
            foreach ($slice->labels() as $field => $label) {
                $labels[$this->prefix($namespace, $field)] = $label;
            }
        }

        return $labels;
    }

    /**
     * Fan a discarded or pruned draft out to the slices whose keys it held.
     */
    public function discard(Model $record, EditDraft $draft): void
    {
        $partitioned = $this->partition($draft->data ?? []);

        foreach ($partitioned['slices'] as $namespace => $values) {
            $this->slices[$namespace]->discard($record, $draft);
        }
    }

    protected function prefix(string $namespace, string $field): string
    {
        return "{$namespace}:{$field}";
    }

    /**
     * Split a flat key set into the resource's own keys and each slice's
     * unprefixed keys. Namespaces are unique per model and the `addon:` form
     * is reserved, so a key matches at most one prefix.
     *
     * @param  array<string, mixed>  $data
     * @return array{resource: array<string, mixed>, slices: array<string, array<string, mixed>>}
     */
    protected function partition(array $data): array
    {
        $resource = [];
        $slices = [];

        foreach ($data as $key => $value) {
            foreach ($this->slices as $namespace => $slice) {
                $prefix = $this->prefix($namespace, '');

                if (str_starts_with($key, $prefix)) {
                    $slices[$namespace][substr($key, strlen($prefix))] = $value;

                    continue 2;
                }
            }

            $resource[$key] = $value;
        }

        return ['resource' => $resource, 'slices' => $slices];
    }
}
