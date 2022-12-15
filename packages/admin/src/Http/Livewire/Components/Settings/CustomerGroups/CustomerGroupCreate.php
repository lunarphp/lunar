<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\CustomerGroups;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\CustomerGroup;
use Illuminate\Support\Str;
use Livewire\Component;

class CustomerGroupCreate extends Component
{
    use Notifies;
    use WithLanguages;

    /**
     * A new instance of the customer group model.
     *
     * @var CustomerGroup
     */
    public CustomerGroup $customerGroup;

    /**
     * Define whether the handle should be treated as manually input.
     *
     * @var bool
     */
    public bool $manualHandle = false;

    /**
     * Called when we mount the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->customerGroup = new CustomerGroup();
        $this->customerGroup->default = false;
    }

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $table = $this->customerGroup->getTable();

        $rules = [
            'customerGroup.handle' => "required|string|unique:$table,handle,{$this->customerGroup->id}|max:255",
        ];

        foreach ($this->languages as $language) {
            $rules["customerGroup.name.{$language->code}"] = ($language->default ? 'required' : 'nullable').'|max:255';
        }

        return $rules;
    }

    /**
     * Create the staff member.
     *
     * @return void
     */
    public function create()
    {
        $this->validate();

        $this->customerGroup->save();

        $this->notify(
            'Customer group successfully created.',
            'hub.customer-groups.index'
        );
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
                $this->customerGroup->name[$this->defaultLanguage->code] ?? null
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
        return view('adminhub::livewire.components.settings.customer-groups.create')
            ->layout('adminhub::layouts.base');
    }
}
