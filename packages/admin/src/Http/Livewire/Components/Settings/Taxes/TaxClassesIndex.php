<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Taxes;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\TaxClass;
use Livewire\Component;
use Livewire\WithPagination;

class TaxClassesIndex extends Component
{
    use WithPagination, Notifies;

    /**
     * The tax class.
     *
     * @var TaxClass|null
     */
    public $taxClass = null;

    public ?int $taxClassId = null;

    public function rules()
    {
        return [
            'taxClass' => 'nullable',
            'taxClass.name' => 'string|required',
            'taxClass.default' => 'boolean|nullable',
        ];
    }

    public function save()
    {
        $this->taxClass->save();

        $this->taxClassId = null;

        $this->notify('Tax Class updated');
    }

    public function editTaxClass($taxClassId)
    {
        $this->taxClass = TaxClass::find($taxClassId);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.taxes.tax-classes.index', [
            'taxClasses' => TaxClass::paginate(),
        ])->layout('adminhub::layouts.base');
    }
}
