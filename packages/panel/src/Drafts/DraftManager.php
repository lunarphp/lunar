<?php

namespace Lunar\Panel\Drafts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Contracts\DraftManager as DraftManagerContract;
use Lunar\Panel\Models\EditDraft;

class DraftManager implements DraftManagerContract
{
    public function __construct(
        protected ValidationFactory $validator,
        protected DatabaseManager $db,
    ) {}

    public function find(Model $draftable, Authenticatable $staff): ?EditDraft
    {
        return EditDraft::query()
            ->where('draftable_type', $draftable->getMorphClass())
            ->where('draftable_id', $draftable->getKey())
            ->where('staff_id', $staff->getAuthIdentifier())
            ->first();
    }

    public function merge(DraftableResource $resource, Model $draftable, Authenticatable $staff, array $data): ?EditDraft
    {
        $data = $resource->normalize($data);

        $this->assertKnownFields($resource, $data);

        if ($data === []) {
            $this->discard($draftable, $staff);

            return null;
        }

        $draft = $this->find($draftable, $staff) ?? $this->newDraft($draftable, $staff);

        $snapshot = $this->snapshotFor($draft, $data, $resource->currentValues($draftable));

        return $this->persist($draft, $data, $snapshot);
    }

    public function discard(Model $draftable, Authenticatable $staff): void
    {
        $this->find($draftable, $staff)?->delete();
    }

    public function commit(DraftableResource $resource, Model $draftable, Authenticatable $staff, array $data, array $rebase = []): CommitResult
    {
        $data = $resource->normalize($data);
        $rebase = $resource->normalize($rebase);

        $this->assertKnownFields($resource, $data);
        $this->assertKnownFields($resource, $rebase);

        $draft = $this->find($draftable, $staff);

        // Overlay the request's final diff onto the stored draft — unlike
        // autosave's wholesale replace, commit must not drop fields another
        // tab may have drafted since this client last loaded.
        $merged = [...($draft?->data ?? []), ...$data];

        if ($merged === []) {
            return CommitResult::committed();
        }

        $current = $resource->currentValues($draftable);

        $snapshot = $this->snapshotFor($draft, $merged, $current);

        // A rebase pins a resolved field's snapshot to the current-DB value
        // the user was shown; if the DB moves again before this commit lands,
        // the field conflicts again rather than silently overwriting.
        foreach ($rebase as $key => $value) {
            if (array_key_exists($key, $merged)) {
                $snapshot[$key] = $value;
            }
        }

        $draft = $this->persist($draft ?? $this->newDraft($draftable, $staff), $merged, $snapshot);

        // Attribute fields would otherwise surface in messages by their raw
        // draft key ("The attribute:hero_cta field is required."). The `.*`
        // variants name the per-value entries of translated/list fields.
        $names = collect($resource->labels())
            ->map(fn (string $label): string => __($label));

        $this->validator
            ->make(
                [...$current, ...$merged],
                $resource->rules($draftable),
                [],
                [...$names, ...$names->mapWithKeys(fn (string $name, string $key) => [$key.'.*' => $name])],
            )
            ->validate();

        if ($conflicts = $this->detectConflicts($resource, $merged, $snapshot, $current)) {
            return CommitResult::conflicted($conflicts);
        }

        $this->db->connection()->transaction(function () use ($resource, $draftable, $current, $merged, $draft): void {
            $resource->commit($draftable, [...$current, ...$merged]);
            $draft->delete();
        });

        return CommitResult::committed();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>  $current
     * @return array<int, array{key: string, label: string, mine: mixed, base: mixed, theirs: mixed}>
     */
    protected function detectConflicts(DraftableResource $resource, array $data, array $snapshot, array $current): array
    {
        $conflicts = [];

        foreach ($data as $key => $mine) {
            $base = $snapshot[$key] ?? null;
            $theirs = $current[$key] ?? null;

            if (! $this->valuesMatch($base, $theirs)) {
                $conflicts[] = [
                    'key' => $key,
                    'label' => __($resource->labels()[$key] ?? $key),
                    'mine' => $mine,
                    'base' => $base,
                    'theirs' => $theirs,
                ];
            }
        }

        return $conflicts;
    }

    /**
     * The base snapshot for a draft about to hold $data: existing snapshot
     * entries are preserved (a field's base stays fixed for the life of the
     * draft), newly-drafted keys capture the record's current value, and keys
     * no longer drafted are dropped.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $current
     * @return array<string, mixed>
     */
    protected function snapshotFor(?EditDraft $draft, array $data, array $current): array
    {
        $existing = $draft?->base_snapshot ?? [];

        $snapshot = [];

        foreach (array_keys($data) as $key) {
            $snapshot[$key] = array_key_exists($key, $existing)
                ? $existing[$key]
                : ($current[$key] ?? null);
        }

        return $snapshot;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $snapshot
     */
    protected function persist(EditDraft $draft, array $data, array $snapshot): EditDraft
    {
        $draft->fill(['data' => $data, 'base_snapshot' => $snapshot])->save();

        return $draft;
    }

    protected function newDraft(Model $draftable, Authenticatable $staff): EditDraft
    {
        return new EditDraft([
            'draftable_type' => $draftable->getMorphClass(),
            'draftable_id' => $draftable->getKey(),
            'staff_id' => $staff->getAuthIdentifier(),
        ]);
    }

    /**
     * Values match when their normalised JSON encodings are identical —
     * associative arrays compare order-insensitively, lists order-sensitively.
     */
    protected function valuesMatch(mixed $a, mixed $b): bool
    {
        return $this->encode($a) === $this->encode($b);
    }

    protected function encode(mixed $value): string
    {
        return (string) json_encode($this->sortKeys($value));
    }

    protected function sortKeys(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $value = array_map(fn (mixed $item): mixed => $this->sortKeys($item), $value);

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    protected function assertKnownFields(DraftableResource $resource, array $data): void
    {
        if ($unknown = array_diff(array_keys($data), $resource->fields())) {
            throw ValidationException::withMessages([
                'data' => 'Unknown draft field ['.implode(', ', $unknown).'].',
            ]);
        }
    }
}
