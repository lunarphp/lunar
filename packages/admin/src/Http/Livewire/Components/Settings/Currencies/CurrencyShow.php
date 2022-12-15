<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Currencies;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\ConfirmsDelete;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Currency;

class CurrencyShow extends Component
{
    use ConfirmsDelete;
    use Notifies;

    /**
     * The instance of the currency we're viewing.
     *
     * @var \Lunar\Models\Currency
     */
    public Currency $currency;

    /**
     * Determine whether to show format info text.
     *
     * @var bool
     */
    public $showFormatInfo = false;

    /**
     * Define the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'currency.code' => 'required|max:255|unique:'.get_class($this->currency).',code,'.$this->currency->id,
            'currency.name' => 'required|max:255',
            'currency.exchange_rate' => 'required|numeric|min:0.0001|max:999999.9999',
            'currency.decimal_places' => 'required|integer|max:4',
            'currency.enabled' => 'nullable',
            'currency.default' => 'nullable',
        ];
    }

    /**
     * Delete the currency.
     *
     * @return void
     */
    public function delete()
    {
        if ($this->canDelete) {
            DB::transaction(function () {
                $this->currency->prices()->delete();
                $this->currency->delete();
            });
            $this->notify('Currency was removed', 'hub.currencies.index');
        }
    }

    /**
     * Returns whether we have met the criteria to allow deletion.
     *
     * @return bool
     */
    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === $this->currency->code;
    }

    /**
     * Toggles the default attribute of the model.
     *
     * @return void
     */
    public function toggleDefault()
    {
        $this->currency->default = ! $this->currency->default;

        // If we're setting the currency to default, force it to be enabled.
        if ($this->currency->default) {
            $this->currency->enabled = true;
        }
    }

    /**
     * Toggles the default attribute of the model.
     *
     * @return void
     */
    public function toggleEnabled()
    {
        $this->currency->enabled = ! $this->currency->enabled;
    }

    /**
     * Update the staff member.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();
        $this->currency->save();
        $this->notify('Currency updated', 'hub.currencies.index');
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.currencies.show')
            ->layout('adminhub::layouts.base');
    }
}
