<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\Actions\Media\DeletesMedia;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Lunar\Core\Contracts\Actions\Media\UpdatesMedia;
use Lunar\Core\Facades\DB;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Forms\FormSlice;
use Lunar\Panel\PanelManager;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A HasMedia record's media, one field per registered collection
 * (`media:images`), each an ordered list of `{id, name, alt, caption, focal}`
 * rows. The first image is the hero. Ids are the record's own media or
 * uploads staged on the staff member's draft for the record. Commit deletes
 * media missing from a list and updates the rest; staged uploads move onto
 * the record after the transaction commits, since file moves cannot roll
 * back, and the order is applied once they have arrived.
 */
abstract class MediaSlice extends FormSlice
{
    public const KEY = 'media';

    public function __construct(
        protected DeletesMedia $deletesMedia,
        protected UpdatesMedia $updatesMedia,
        protected ReordersMedia $reordersMedia,
        protected DraftManager $drafts,
        protected PanelManager $panel,
    ) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function fields(Model $record): array
    {
        /** @var HasMedia&Model $record */
        return $record->getRegisteredMediaCollections()->pluck('name')->map(fn ($name): string => (string) $name)->all();
    }

    public function currentValues(Model $record): array
    {
        /** @var HasMedia&Model $record */
        $values = [];

        foreach ($this->fields($record) as $collection) {
            $values[$collection] = $record->exists
                ? $record->getMedia($collection)->map(fn (Media $media): array => $this->row($media))->values()->all()
                : [];
        }

        return $values;
    }

    public function normalize(array $data): array
    {
        $text = fn (mixed $value): ?string => is_string($value) && trim($value) !== '' ? trim($value) : null;

        foreach ($data as $collection => $rows) {
            $data[$collection] = array_values(array_map(fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : null,
                'name' => $text($row['name'] ?? null),
                'alt' => $text($row['alt'] ?? null),
                'caption' => $text($row['caption'] ?? null),
                'focal' => is_array($row['focal'] ?? null) && isset($row['focal']['x'], $row['focal']['y'])
                    ? ['x' => (int) $row['focal']['x'], 'y' => (int) $row['focal']['y']]
                    : null,
            ], array_filter((array) $rows, 'is_array')));
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var HasMedia&Model $record */
        $staged = $this->staged($record);
        $rules = [];

        foreach ($this->fields($record) as $collection) {
            $allowed = [
                ...($record->exists ? $record->getMedia($collection)->modelKeys() : []),
                ...$staged->where('collection_name', $collection)->modelKeys(),
            ];

            $rules[$collection] = ['nullable', 'array'];
            $rules[$collection.'.*'] = ['array'];
            $rules[$collection.'.*.id'] = ['required', 'integer', 'distinct', Rule::in($allowed)];
            $rules[$collection.'.*.name'] = ['nullable', 'string', 'max:255'];
            $rules[$collection.'.*.alt'] = ['nullable', 'string', 'max:255'];
            $rules[$collection.'.*.caption'] = ['nullable', 'string', 'max:1000'];
            $rules[$collection.'.*.focal'] = ['nullable', 'array:x,y'];
            $rules[$collection.'.*.focal.x'] = ['required_with:'.$collection.'.*.focal', 'integer', 'between:0,100'];
            $rules[$collection.'.*.focal.y'] = ['required_with:'.$collection.'.*.focal', 'integer', 'between:0,100'];
        }

        return $rules;
    }

    public function commit(Model $record, array $values): void
    {
        /** @var HasMedia&Model $record */
        $staged = $this->staged($record)->keyBy('id');
        $moves = [];

        foreach ($this->normalize($values) as $collection => $rows) {
            if (! in_array($collection, $this->fields($record), true)) {
                continue;
            }

            $existing = $record->getMedia($collection)->keyBy('id');
            $listed = array_column($rows, 'id');

            $existing->except($listed)->each(fn (Media $media) => $this->deletesMedia->execute($media));

            foreach ($rows as $row) {
                if ($media = $existing->get($row['id'])) {
                    if ($this->row($media) !== $row) {
                        $this->updatesMedia->execute($media, $this->properties($row));
                    }
                }
            }

            $moves[$collection] = $rows;
        }

        // A committed draft is deleted quietly, keeping its staged media for
        // the move; staged uploads the lists dropped are deleted here.
        DB::afterCommit(function () use ($record, $staged, $moves): void {
            foreach ($moves as $collection => $rows) {
                $ids = [];

                foreach ($rows as $row) {
                    $media = $staged->get($row['id']);

                    if ($media) {
                        // The order decides the primary, not the staging flag.
                        $media->forgetCustomProperty('primary')->saveQuietly();
                        $media = $media->move($record, $collection);
                        $this->updatesMedia->execute($media, $this->properties($row));
                    }

                    $ids[] = $media ? $media->id : $row['id'];
                }

                $record->unsetRelation('media');

                if ($ids !== []) {
                    $this->reordersMedia->execute($record, $ids, $collection);
                }
            }

            $listed = collect($moves)->flatMap(fn (array $rows): array => array_column($rows, 'id'))->all();

            $staged->except($listed)->each(fn (Media $media) => $media->delete());
        });
    }

    public function labels(): array
    {
        $labels = [];

        foreach ($this->fields(new ($this->model())) as $collection) {
            $labels[$collection] = 'panel::media.title';
            $labels[$collection.'.*.id'] = 'panel::media.title';
            $labels[$collection.'.*.name'] = 'panel::media.field_name';
            $labels[$collection.'.*.alt'] = 'panel::media.field_alt';
            $labels[$collection.'.*.caption'] = 'panel::media.field_caption';
            $labels[$collection.'.*.focal'] = 'panel::media.focal_point';
        }

        return $labels;
    }

    /**
     * Uploads staged on the current staff member's draft for the record.
     *
     * @return EloquentCollection<int, Media>
     */
    protected function staged(Model $record): EloquentCollection
    {
        $staff = $this->panel->user();
        $draft = $staff && $record->exists ? $this->drafts->find($record, $staff) : null;

        return $draft ? $draft->media()->get() : new EloquentCollection;
    }

    /** @return array{id: int, name: ?string, alt: ?string, caption: ?string, focal: ?array{x: int, y: int}} */
    protected function row(Media $media): array
    {
        $focal = $media->getCustomProperty('focal');

        return [
            'id' => (int) $media->id,
            'name' => $media->getCustomProperty('name'),
            'alt' => $media->getCustomProperty('alt'),
            'caption' => $media->getCustomProperty('caption'),
            'focal' => is_array($focal) ? ['x' => (int) $focal['x'], 'y' => (int) $focal['y']] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function properties(array $row): array
    {
        return array_filter(
            ['name' => $row['name'], 'alt' => $row['alt'], 'caption' => $row['caption'], 'focal' => $row['focal']],
            fn (mixed $value, string $key): bool => $key !== 'focal' || $value !== null,
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
