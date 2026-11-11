<?php

namespace Lunar\Panel\Forms;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Contracts\FormSlice;

/**
 * The form slices registered for one model, with the prefixing that turns
 * their bare field names into `{namespace}:{field}` keys. The only place a
 * slice key is prefixed or unprefixed: slices deal in their own names, the
 * form sees one flat key space, and neither can reach the other's keys.
 */
class SliceSet
{
    /**
     * @param  array<string, FormSlice>  $slices  keyed by namespace, in registration order
     */
    public function __construct(protected array $slices) {}

    public function isEmpty(): bool
    {
        return $this->slices === [];
    }

    /** @return array<string, FormSlice> */
    public function slices(): array
    {
        return $this->slices;
    }

    public function slice(string $namespace): FormSlice
    {
        return $this->slices[$namespace];
    }

    public function prefix(string $namespace, string $field = ''): string
    {
        return "{$namespace}:{$field}";
    }

    /** @return array<int, string> */
    public function fields(Model $record): array
    {
        $fields = [];

        foreach ($this->slices as $namespace => $slice) {
            foreach ($slice->fields($record) as $field) {
                $fields[] = $this->prefix($namespace, $field);
            }
        }

        return $fields;
    }

    /**
     * The prefixed current values of every slice: what a page seeds its
     * form with.
     *
     * @return array<string, mixed>
     */
    public function values(Model $record): array
    {
        $values = [];

        foreach ($this->slices as $namespace => $slice) {
            foreach ($slice->currentValues($record) as $field => $value) {
                $values[$this->prefix($namespace, $field)] = $value;
            }
        }

        return $values;
    }

    /** @return array<string, mixed> */
    public function rules(Model $record): array
    {
        $rules = [];

        foreach ($this->slices as $namespace => $slice) {
            foreach ($slice->rules($record) as $field => $fieldRules) {
                $rules[$this->prefix($namespace, $field)] = $fieldRules;
            }
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function labels(): array
    {
        $labels = [];

        foreach ($this->slices as $namespace => $slice) {
            foreach ($slice->labels() as $field => $label) {
                $labels[$this->prefix($namespace, $field)] = $label;
            }
        }

        return $labels;
    }

    /**
     * Normalise the slice keys in a flat key set, leaving other keys as they
     * are.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(array $data): array
    {
        $partitioned = $this->partition($data);

        $normalized = $partitioned['rest'];

        foreach ($partitioned['slices'] as $namespace => $values) {
            foreach ($this->slices[$namespace]->normalize($values) as $field => $value) {
                $normalized[$this->prefix($namespace, $field)] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Split a flat key set into each slice's unprefixed keys and the rest.
     * Namespaces are unique per model and the `addon:` form is reserved, so
     * a key matches at most one prefix.
     *
     * @param  array<string, mixed>  $data
     * @return array{rest: array<string, mixed>, slices: array<string, array<string, mixed>>}
     */
    public function partition(array $data): array
    {
        $rest = [];
        $slices = [];

        foreach ($data as $key => $value) {
            foreach (array_keys($this->slices) as $namespace) {
                $prefix = $this->prefix($namespace);

                if (str_starts_with((string) $key, $prefix)) {
                    $slices[$namespace][substr($key, strlen($prefix))] = $value;

                    continue 2;
                }
            }

            $rest[$key] = $value;
        }

        return ['rest' => $rest, 'slices' => $slices];
    }
}
