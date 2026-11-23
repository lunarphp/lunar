<?php

namespace Lunar\Panel\Drafts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
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

    public function stagingDraft(Model $draftable, Authenticatable $staff): EditDraft
    {
        $draft = $this->find($draftable, $staff);

        if ($draft) {
            return $draft;
        }

        try {
            $draft = $this->newDraft($draftable, $staff);
            $draft->fill(['data' => [], 'base_snapshot' => [], 'version' => 0])->save();

            return $draft;
        } catch (UniqueConstraintViolationException) {
            // A parallel upload created it first.
            return $this->find($draftable, $staff) ?? throw new \LogicException('Draft vanished while staging.');
        }
    }

    public function merge(DraftableResource $resource, Model $draftable, Authenticatable $staff, array $data, array $base = [], ?int $version = null): ?EditDraft
    {
        $data = $resource->normalize($data);

        $this->assertKnownFields($resource, $draftable, $data);

        $draft = $this->find($draftable, $staff);

        $this->assertVersion($draft, $version);

        if ($data === []) {
            $draft?->delete();

            return null;
        }

        $snapshot = $this->snapshotFor($draft, $data, $this->baseValues($resource, $data, $base), $resource->currentValues($draftable));

        return $this->persist($draft ?? $this->newDraft($draftable, $staff), $data, $snapshot);
    }

    public function discard(Model $draftable, Authenticatable $staff, ?int $version = null): void
    {
        $draft = $this->find($draftable, $staff);

        $this->assertVersion($draft, $version);

        $draft?->delete();
    }

    public function commit(DraftableResource $resource, Model $draftable, Authenticatable $staff, array $data, array $rebase = [], array $base = [], ?int $version = null): CommitResult
    {
        $data = $resource->normalize($data);
        $rebase = $resource->normalize($rebase);

        $this->assertKnownFields($resource, $draftable, $data);
        $this->assertKnownFields($resource, $draftable, $rebase);

        $draft = $this->find($draftable, $staff);

        $this->assertVersion($draft, $version);

        // The client's diff is the whole commit. Overlaying the stored draft
        // would commit keys the client now considers clean: a field reverted
        // just before Save, or a conflict resolved to the current value.
        if ($data === []) {
            $draft?->delete();

            return CommitResult::committed();
        }

        $current = $resource->currentValues($draftable);

        $snapshot = $this->snapshotFor($draft, $data, $this->baseValues($resource, $data, $base), $current);

        // A rebase pins a resolved field's snapshot to the current-DB value
        // the user was shown; if the DB moves again before this commit lands,
        // the field conflicts again rather than silently overwriting.
        foreach ($rebase as $key => $value) {
            if (array_key_exists($key, $data)) {
                $snapshot[$key] = $value;
            }
        }

        // Persisted before validating, so a refused commit leaves the edits
        // drafted where the next visit restores them.
        $draft = $this->persist($draft ?? $this->newDraft($draftable, $staff), $data, $snapshot);

        // Attribute fields would otherwise surface in messages by their raw
        // draft key ("The attribute:hero_cta field is required."). The `.*`
        // variants name the per-value entries of translated/list fields.
        $names = collect($resource->labels())
            ->map(fn (string $label): string => __($label));

        $this->validator
            ->make(
                [...$current, ...$data],
                $resource->rules($draftable),
                [],
                [...$names, ...$names->mapWithKeys(fn (string $name, string $key) => [$key.'.*' => $name])],
            )
            ->validate();

        return $this->db->connection()->transaction(function () use ($resource, $draftable, $data, $snapshot, $draft): CommitResult {
            // Conflicts are detected against the record as locked here, so a
            // commit landing between the check and the write is caught.
            $draftable->newQuery()->whereKey($draftable->getKey())->lockForUpdate()->first();
            $draftable->refresh();

            $current = $resource->currentValues($draftable);

            if ($conflicts = $this->detectConflicts($resource, $data, $snapshot, $current)) {
                return CommitResult::conflicted($conflicts);
            }

            $resource->commit($draftable, [...$current, ...$data]);

            // Quietly: a committed draft is consumed, not discarded, so the
            // slice discard hooks wired to the deleting event must not fire.
            $draft->deleteQuietly();

            return CommitResult::committed();
        });
    }

    /**
     * A key conflicts when the record moved away from its base and does not
     * already hold the drafted value; when both sides agree there is nothing
     * to choose between.
     *
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

            if (! $this->valuesMatch($base, $theirs) && ! $this->valuesMatch($mine, $theirs)) {
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
     * The client-supplied base for the drafted keys, normalised like the data
     * so it compares against current values on equal terms.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    protected function baseValues(DraftableResource $resource, array $data, array $base): array
    {
        $base = array_intersect_key($base, $data);

        return $base === [] ? [] : array_intersect_key($resource->normalize($base), $base);
    }

    /**
     * The base snapshot for a draft about to hold $data: existing snapshot
     * entries are preserved (a field's base stays fixed for the life of the
     * draft), newly-drafted keys take the value the client showed before the
     * edit, falling back to the record's current value, and keys no longer
     * drafted are dropped.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $current
     * @return array<string, mixed>
     */
    protected function snapshotFor(?EditDraft $draft, array $data, array $base, array $current): array
    {
        $existing = $draft?->base_snapshot ?? [];

        $snapshot = [];

        foreach (array_keys($data) as $key) {
            $snapshot[$key] = match (true) {
                array_key_exists($key, $existing) => $existing[$key],
                array_key_exists($key, $base) => $base[$key],
                default => $current[$key] ?? null,
            };
        }

        return $snapshot;
    }

    /**
     * @throws StaleDraftException
     */
    protected function assertVersion(?EditDraft $draft, ?int $version): void
    {
        $stored = (int) ($draft?->version ?? 0);

        if ($version !== null && $version !== $stored) {
            throw new StaleDraftException($stored);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $snapshot
     */
    protected function persist(EditDraft $draft, array $data, array $snapshot): EditDraft
    {
        $draft->fill([
            'data' => $data,
            'base_snapshot' => $snapshot,
            'version' => (int) $draft->version + 1,
        ])->save();

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
    protected function assertKnownFields(DraftableResource $resource, Model $draftable, array $data): void
    {
        $unknown = array_filter(
            array_keys($data),
            fn (string|int $key): bool => ! $resource->acceptsField($draftable, (string) $key),
        );

        if ($unknown !== []) {
            throw ValidationException::withMessages([
                'data' => 'Unknown draft field ['.implode(', ', $unknown).'].',
            ]);
        }
    }
}
