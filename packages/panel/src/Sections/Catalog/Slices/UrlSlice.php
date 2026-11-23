<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\Actions\Urls\CreatesUrl;
use Lunar\Core\Contracts\Actions\Urls\DeletesUrl;
use Lunar\Core\Contracts\Actions\Urls\UpdatesUrl;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Url;
use Lunar\Panel\Forms\FormSlice;

/**
 * A HasUrls record's storefront slugs, one field per language code
 * (`url:en`), each an ordered list of `{id, slug, default}` rows. New rows
 * carry a null id and are created at commit; rows missing from the list are
 * deleted. Each language keeps exactly one default.
 */
abstract class UrlSlice extends FormSlice
{
    public const KEY = 'url';

    /** @var array<string, int>|null language code to id, read once per request */
    protected ?array $languages = null;

    public function __construct(
        protected CreatesUrl $createsUrl,
        protected UpdatesUrl $updatesUrl,
        protected DeletesUrl $deletesUrl,
    ) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function fields(Model $record): array
    {
        return array_map('strval', array_keys($this->languages()));
    }

    public function currentValues(Model $record): array
    {
        $rows = $record->exists
            ? $record->urls()->orderByDesc('default')->orderBy('id')->get()->groupBy('language_id')
            : collect();

        $values = [];

        foreach ($this->languages() as $code => $languageId) {
            $values[$code] = $rows->get($languageId, collect())
                ->map(fn (Url $url): array => ['id' => $url->id, 'slug' => $url->slug, 'default' => (bool) $url->default])
                ->values()
                ->all();
        }

        return $values;
    }

    /**
     * Rows in the shape currentValues() reports. A language with rows always
     * has exactly one default: the first row marked default, else the first.
     */
    public function normalize(array $data): array
    {
        foreach ($data as $code => $rows) {
            $rows = array_values(array_map(fn (mixed $row): array => [
                'id' => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                'slug' => trim((string) ($row['slug'] ?? '')),
                'default' => (bool) ($row['default'] ?? false),
            ], array_filter((array) $rows, 'is_array')));

            $default = array_search(true, array_column($rows, 'default'), true);

            foreach ($rows as $index => $row) {
                $rows[$index]['default'] = $index === ($default === false ? 0 : $default);
            }

            $data[$code] = $rows;
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        $owned = $record->exists
            ? $record->urls()->get(['id', 'language_id'])->groupBy('language_id')
            : collect();

        $rules = [];

        foreach ($this->languages() as $code => $languageId) {
            // Slugs route the storefront per element type and language, so a
            // slug must be free among the type's other elements; this
            // record's own rows may swap slugs within the draft.
            $unique = Rule::unique((new Url)->getTable(), 'slug')
                ->where('element_type', $record->getMorphClass())
                ->where('language_id', $languageId);

            if ($record->exists) {
                $unique->where(fn ($query) => $query->where('element_id', '!=', $record->getKey()));
            }

            $rules[$code] = ['nullable', 'array'];
            $rules[$code.'.*'] = ['array'];
            $rules[$code.'.*.id'] = ['nullable', 'integer', Rule::in($owned->get($languageId, collect())->pluck('id')->all())];
            $rules[$code.'.*.slug'] = ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'distinct', $unique];
            $rules[$code.'.*.default'] = ['boolean'];
        }

        return $rules;
    }

    /**
     * Deletes first, then slug updates and creates, then promotes each
     * language's chosen default, so the core actions' own default handling
     * never leaves a row the draft did not pick as the default.
     */
    public function commit(Model $record, array $values): void
    {
        // The URL actions read each row's element; hand them the record.
        $existing = $record->urls()->get()->each->setRelation('element', $record)->keyBy('id');

        foreach ($this->normalize($values) as $code => $rows) {
            $languageId = $this->languages()[$code] ?? null;

            if ($languageId === null) {
                continue;
            }

            $kept = array_filter(array_column($rows, 'id'));

            $existing
                ->filter(fn (Url $url): bool => $url->language_id === $languageId && ! in_array($url->id, $kept, true))
                ->each(fn (Url $url) => $this->deletesUrl->execute($url));

            $default = null;

            foreach ($rows as $row) {
                $url = $row['id'] ? $existing->get($row['id']) : null;

                if ($url) {
                    if ($url->slug !== $row['slug']) {
                        $this->updatesUrl->execute($url, ['slug' => $row['slug']]);
                    }
                } else {
                    $url = $this->createsUrl->execute($record, ['language_id' => $languageId, 'slug' => $row['slug']]);
                }

                if ($row['default']) {
                    $default = $url;
                }
            }

            if ($default && ! $default->refresh()->setRelation('element', $record)->default) {
                $this->updatesUrl->execute($default, ['default' => true]);
            }
        }
    }

    public function labels(): array
    {
        $labels = [];

        foreach (array_keys($this->languages()) as $code) {
            $labels[$code] = 'panel::urls.title';
            $labels[$code.'.*.id'] = 'panel::urls.title';
            $labels[$code.'.*.slug'] = 'panel::urls.field_url_slug';
            $labels[$code.'.*.default'] = 'panel::urls.column_default';
        }

        return $labels;
    }

    /** @return array<string, int> */
    protected function languages(): array
    {
        return $this->languages ??= Language::query()
            ->orderByDesc('default')
            ->orderBy('code')
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
