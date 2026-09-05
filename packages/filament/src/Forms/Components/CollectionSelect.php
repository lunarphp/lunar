<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Filament\Forms\Components\Concerns\AppliesToExistingSelect;
use Lunar\Filament\Forms\Components\Concerns\ExcludesAttachedRecords;
use Lunar\Filament\Forms\Components\Concerns\SearchesLunarRecords;

class CollectionSelect extends Select
{
    use AppliesToExistingSelect;
    use ExcludesAttachedRecords;
    use SearchesLunarRecords;

    protected ?Collection $excludeDescendantsOf = null;

    protected ?Collection $excludeSelf = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.collection.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.collection.placeholder'));
        $this->searchable();
        $this->modifyOptionsQueryUsing(fn ($query) => method_exists($query, 'with') ? $query->with(['group', 'ancestors']) : $query);
        $this->getSearchResultsUsing(fn (string $search): array => $this->searchLunarRecords($search));
        $this->getOptionLabelUsing(function ($value): ?string {
            $model = $this->lunarModel();
            $record = $model::with(['group', 'ancestors'])->find($value);

            return $record ? $this->optionLabel($record) : null;
        });

        $this->filterOptionsUsing(function (Model $record): bool {
            if ($this->excludeSelf && $this->excludeSelf->getKey() === $record->getKey()) {
                return true;
            }

            if ($this->excludeDescendantsOf && $record->isDescendantOf($this->excludeDescendantsOf)) {
                return true;
            }

            return false;
        });
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Collection::class;
    }

    public function excludeDescendantsOf(Collection $collection): static
    {
        $this->excludeDescendantsOf = $collection;

        return $this;
    }

    public function excludeSelf(Collection $collection): static
    {
        $this->excludeSelf = $collection;

        return $this;
    }

    public function withinGroup(CollectionGroup $group): static
    {
        $this->modifyOptionsQueryUsing(function ($query) use ($group) {
            if (method_exists($query, 'where')) {
                return $query->where('collection_group_id', $group->getKey());
            }

            return $query;
        });

        return $this;
    }

    public function optionLabelRelations(): array
    {
        return ['group', 'ancestors'];
    }

    public function optionLabel(Model $record): string
    {
        $breadcrumb = $record->breadcrumb ?? collect();

        // Group first: two collections can share a name and an ancestry in
        // different groups, and the trail alone leaves them indistinguishable.
        $trail = collect([$record->group?->name])
            ->concat($breadcrumb)
            ->push($record->translate('name'))
            ->filter()
            ->implode(' > ');

        return $trail !== '' ? $trail : $record->translate('name');
    }
}
