<?php

namespace Lunar\Hub\Http\Livewire\Components\Collections;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\CollectionGroup;
use Illuminate\Support\Str;
use Livewire\Component;

class SideMenu extends Component
{
    use Notifies;

    public bool $showCreateModal = false;

    public $name = '';

    public $currentGroup = null;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:'.CollectionGroup::class.',name',
        ];
    }

    public function createCollectionGroup()
    {
        $this->validate();
        $newGroup = CollectionGroup::create([
            'name'   => $this->name,
            'handle' => Str::slug($this->name),
        ]);

        $redirect = null;

        if (CollectionGroup::count() == 1) {
            $redirect = 'hub.collection-groups.show';
        }

        $this->notify('Collection group created', $redirect, [
            'group' => $newGroup,
        ]);

        $this->name = '';
        $this->showCreateModal = false;
    }

    public function getCollectionGroupsProperty()
    {
        return CollectionGroup::orderBy('name')->get();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.collections.sidemenu')
            ->layout('adminhub::layouts.app');
    }
}
