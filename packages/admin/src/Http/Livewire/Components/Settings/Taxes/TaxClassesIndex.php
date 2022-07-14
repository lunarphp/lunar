<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Taxes;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\TaxClass;
use GetCandy\Models\TaxZone;
use Livewire\Component;
use Livewire\WithPagination;

class TaxClassesIndex extends Component
{
    use WithPagination, Notifies;

    /**
     * The tax class
     *
     * @var TaxClass|null
     */
    public ?TaxClass $taxClass = null;

    public ?int $taxClassId = null;

    public function rules()
    {
        return [
            'taxClass.name' => 'string|required',
            'taxClass.default' => 'required',
        ];
    }

    public function save()
    {
        $this->taxClass->save();

        $this->taxClassId = null;

        $this->notify('Tax Class updated');
    }

    public function updatedTaxClassId($val)
    {
        $this->taxClass = $val ? TaxClass::find($val) : null;
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
