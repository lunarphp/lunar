<?php

namespace Lunar\Panel\Search\Sources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Collection;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Sections\Catalog\CatalogSection;
use Lunar\Panel\Support\Position;

class CollectionSearchSource extends SearchSource
{
    public function key(): string
    {
        return 'collections';
    }

    public function label(): string
    {
        return __('panel::search.source_collections');
    }

    public function icon(): string
    {
        return 'folder';
    }

    public function permission(): string
    {
        return CatalogSection::COLLECTIONS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(30);
    }

    /** @return Builder<Collection> */
    public function query(): Builder
    {
        return Collection::query()->with('group:id,name');
    }

    public function applyTerm(Builder $query, string $token): void
    {
        $like = "%{$token}%";

        $query->where('name', 'like', $like)
            ->orWhereHas('urls', fn (Builder $query) => $query->where('slug', 'like', $like));
    }

    /** @param Collection $model */
    public function row(Model $model): array
    {
        return [
            'id' => $model->id,
            // Which group a collection belongs to; two groups can hold
            // same-named collections.
            'hint' => $model->group?->name,
            'label' => (string) $model->translate('name'),
            'url' => route('panel.collections.edit', $model),
        ];
    }
}
