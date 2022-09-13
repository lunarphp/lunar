<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Currencies;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Currency;

class CurrencyCreate extends Component
{
    use Notifies;

    /**
     * The empty currency model.
     *
     * @var Currency
     */
    public Currency $currency;

    /**
     * Determine whether to show format info text.
     *
     * @var bool
     */
    public $showFormatInfo = false;

    public function mount()
    {
        $this->currency = new Currency();
    }

    /**
     * Define the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'currency.code'           => 'required|max:255|unique:'.get_class($this->currency).',code',
            'currency.name'           => 'required|max:255',
            'currency.exchange_rate'  => 'required|numeric|min:0.0001|max:999999.9999',
            'currency.decimal_places' => 'required|integer|max:4',
            'currency.enabled'        => 'nullable',
            'currency.default'        => 'nullable',
        ];
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
     * Create the currency.
     *
     * @return void
     */
    public function create()
    {
        $this->validate();

        $this->currency->default = (bool) $this->currency->default;
        $this->currency->enabled = (bool) $this->currency->enabled;

        $this->currency->save();
        $this->notify(
            __('adminhub::settings.currencies.form.notify.created'),
            'hub.currencies.index'
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.currencies.create')
            ->layout('adminhub::layouts.base');
    }
}
