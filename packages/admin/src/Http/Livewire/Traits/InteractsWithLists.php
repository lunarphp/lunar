<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Models\AttributeGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait InteractsWithLists
{
    protected function filterOnlyAssignedGroups(Model $model): bool
    {
        if (! $this->productType->attribute_data) {
            return false;
        }

        $group = AttributeGroup::whereHandle(Str::replace('model_', '', $this->activeTab))->first();
        $data = collect($this->productType->attribute_data->get($group->handle));

        return $data->keys()->contains($model->id);
    }

    protected function sortFilterGroupValues(Model $group, string $handle): Collection
    {
        $groupValuePositions = collect($this->productType->attribute_data->get($handle))
            ->get($group->id)['values'];

        return $group->values
            ->filter(fn (Model $groupValue) => collect($groupValuePositions)->contains($groupValue->id))
            ->sortBy(fn (Model $groupValue) => collect($groupValuePositions)->search($groupValue->id));
    }

    /**
     * @todo Refactor to use action
     *
     * @param  array  $group
     */
    public function sortableGroups(array $group): void
    {
        $handle = AttributeGroup::whereHandle(
            Str::replace('model_', '', $this->activeTab)
        )->value('handle');

        $sortedGroupIds = array_map('intval', collect($group['items'])->pluck('id')->toArray());
        $data = collect($this->productType->attribute_data->get($handle))->put('groupIds', $sortedGroupIds);

        $this->productType->attribute_data->put($handle, $data);
        $this->productType->save();
    }

    /**
     * @todo Refactor to use action
     *
     * @param  array  $group
     */
    public function sortableGroupValues(array $group): void
    {
        $handle = AttributeGroup::whereHandle(
            Str::replace('model_', '', $this->activeTab)
        )->value('handle');

        $sortedGroupValuesIds = [
            'values' => array_map('intval', collect($group['items'])->pluck('id')->toArray()),
        ];
        $data = collect($this->productType->attribute_data->get($handle))->put($group['owner'], $sortedGroupValuesIds);
        $this->productType->attribute_data->put($handle, $data);

        $this->productType->save();
    }

    public function detachGroup(): void
    {
        $group = AttributeGroup::whereHandle(Str::replace('model_', '', $this->activeTab))->first();
        $data = collect($this->productType->attribute_data->get($group->handle));
        $groupIds = collect($data->get('groupIds'));
        $data->put('groupIds', $groupIds->filter(fn ($id) => $id !== $this->removeGroupId));
        $data->pull($this->removeGroupId);

        $this->productType->attribute_data->put($group->handle, $data);
        $this->productType->save();
        $this->removeGroupId = null;
    }

    public function detachGroupValue(): void
    {
        if (! $groupValue = $this->removeGroupValueId) {
            return;
        }

        [$groupValueId, $groupId] = Str::of($groupValue)->explode('_');
        $group = AttributeGroup::whereHandle(Str::replace('model_', '', $this->activeTab))->first();
        $data = collect($this->productType->attribute_data->get($group->handle));
        $values = collect($data->get($groupId)['values'])->filter(fn ($id) => (int) $id !== (int) $groupValueId);

        $data->put($groupId, ['values' => $values]);
        $this->productType->attribute_data->put($group->handle, $data);
        $this->productType->save();

        $this->removeGroupValueId = null;
    }

    public function attachToGroup()
    {
        if (! $this->selectedGroupValueId) {
            $this->attachValueToGroupId = null;

            return;
        }

        $group = AttributeGroup::whereHandle(Str::replace('model_', '', $this->activeTab))->first();
        $data = collect($this->productType->attribute_data->get($group->handle));
        $values = collect($data->get($this->attachValueToGroupId)['values'])->push($this->selectedGroupValueId);

        $data->put($this->attachValueToGroupId, ['values' => $values]);
        $this->productType->attribute_data->put($group->handle, $data);
        $this->productType->save();

        $this->attachValueToGroupId = null;
        $this->selectedGroupValueId = null;
    }
}
