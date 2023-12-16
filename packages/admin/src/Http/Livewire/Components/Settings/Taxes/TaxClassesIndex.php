<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Taxes;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Facades\DB;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\TaxClass;

class TaxClassesIndex extends Component
{
    use Notifies, WithPagination;

    /**
     * The TaxClass to edit.
     */
    public ?TaxClass $taxClass = null;

    /**
     * The ID of the TaxClass to edit.
     *
     * @var int|string
     */
    public $taxClassId = null;

    /**
     * Whether the TaxClass should be deleted on save.
     */
    public bool $deleting = false;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'taxClass' => 'nullable',
            'taxClass.name' => 'string|required',
            'taxClass.default' => 'boolean|nullable',
        ];
    }

    /**
     * Listener when tax class id is updated.
     *
     * @param  string|int  $val
     * @return void
     */
    public function updatedTaxClassId($val)
    {
        $this->resetErrorBag();

        if ($val == 'new') {
            $this->taxClass = new TaxClass([
                'default' => false,
            ]);
        } else {
            $this->taxClass = $val ? TaxClass::find($val) : null;
        }
        $this->deleting = false;
    }

    /**
     * Toggle the tax class default value.
     *
     * @return void
     */
    public function toggleDefault()
    {
        $this->taxClass->default = ! $this->taxClass->default;
    }

    /**
     * Get the variant count for the tax class.
     *
     * @return int
     */
    public function getVariantCountProperty()
    {
        if (! $this->taxClass) {
            return 0;
        }

        return $this->taxClass->productVariants()->count();
    }

    /**
     * Whether we should disable the default toggle.
     *
     * @return bool
     */
    public function getShouldDisableDefaultProperty()
    {
        if (! $this->taxClass || ! $this->taxClass->id) {
            return false;
        }

        $existingDefault = TaxClass::whereDefault(true)
            ->first();

        return $this->taxClass->default && $existingDefault?->id == $this->taxClass->id;
    }

    /**
     * Save the TaxClass.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->deleting) {
                $this->taxClass->taxRateAmounts()->delete();
                $this->taxClass->delete();
            } else {
                $this->taxClass->save();
            }
        });

        $this->taxClassId = null;

        $this->taxClass = null;

        $this->notify(
            __(
                $this->deleting ? 'adminhub::notifications.tax_class.deleted' : 'adminhub::notifications.tax_class.saved'
            )
        );
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
