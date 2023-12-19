<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\CustomerGroups;

use Illuminate\Support\Str;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\CustomerGroup;

class CustomerGroupShow extends Component
{
    use Notifies;
    use WithLanguages;

    /**
     * The current customer group we're showing.
     */
    public CustomerGroup $customerGroup;

    /**
     * Defines the confirmation text when deleting a customer group.
     *
     * @var string|null
     */
    public $deleteConfirm = null;

    /**
     * Define whether the handle should be treated as manually input.
     */
    public bool $manualHandle = true;

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $rules = [
            'customerGroup.name' => 'required|string|unique:'.CustomerGroup::class.",name,{$this->customerGroup->id}",
            'customerGroup.handle' => 'required|string|unique:'.CustomerGroup::class.",handle,{$this->customerGroup->id}|max:255",
            'customerGroup.default' => 'nullable|boolean',
        ];

        return $rules;
    }

    /**
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $this->customerGroup->save();

        $this->notify(
            'Customer group successfully updated.',
            'hub.customer-groups.index'
        );
    }

    /**
     * Soft deletes a customerGroup.
     *
     * @return void
     */
    public function delete()
    {
        if (! $this->canDelete) {
            return;
        }

        // @todo Detach all customers from the group then delete

        $this->customerGroup->delete();

        $this->notify(
            'Customer group successfully deleted.',
            'hub.customer-groups.index'
        );
    }

    /**
     * Returns whether we have met the criteria to allow deletion.
     *
     * @return bool
     */
    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === $this->customerGroup->name;
    }

    /**
     * Formats the handle from the name.
     *
     * @return void
     */
    public function formatHandle()
    {
        if (! $this->manualHandle) {
            $this->customerGroup->handle = Str::handle(
                $this->customerGroup->name
            );
        }
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.customer-groups.show')
            ->layout('adminhub::layouts.base');
    }
}
