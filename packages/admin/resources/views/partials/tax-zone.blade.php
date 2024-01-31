<div class="space-y-4">
    <div class="overflow-hidden shadow sm:rounded-md">
        <x-hub::modal.dialog form="deleteZone"
                             wire:model="taxZoneToRemove">
            <x-slot name="title">
                {{ __('adminhub::settings.taxes.tax-zones.confirm_delete.title') }}
            </x-slot>

            <x-slot name="content">
                <x-hub::alert level="danger">
                    {{ __('adminhub::settings.taxes.tax-zones.confirm_delete.message') }}
                </x-hub::alert>
            </x-slot>

            <x-slot name="footer">
                <x-hub::button type="button"
                               wire:click.prevent="$set('taxZoneToRemove', null)"
                               theme="gray">
                    {{ __('adminhub::global.cancel') }}
                </x-hub::button>

                <x-hub::button type="submit"
                               theme="danger">
                    {{ __('adminhub::global.remove') }}
                </x-hub::button>
            </x-slot>
        </x-hub::modal.dialog>

        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <div class="space-y-4">
                <x-hub::input.group :label="__('adminhub::inputs.name')"
                                    for="name"
                                    :error="$errors->first('taxZone.name')"
                                    required>

                    <x-hub::input.text wire:model="taxZone.name"
                                       name="name"
                                       id="name"
                                       :error="$errors->first('taxZone.name')" />
                </x-hub::input.group>

                <x-hub::input.group :label="__('adminhub::settings.taxes.tax-zones.price_display.label')"
                                    for="priceDisplay"
                                    :error="$errors->first('taxZone.price_display')"
                                    required>

                    <x-hub::input.select wire:model="taxZone.price_display"
                                         id="priceDisplay">
                        <option value="include_tax">
                            {{ __('adminhub::settings.taxes.tax-zones.price_display.incl_tax') }}
                        </option>

                        <option value="exclude_tax">
                            {{ __('adminhub::settings.taxes.tax-zones.price_display.excl_tax') }}
                        </option>
                    </x-hub::input.select>
                </x-hub::input.group>

                <div class="grid grid-cols-2 gap-4">
                    <x-hub::input.group :label="__('adminhub::inputs.active.label')"
                                        for="active"
                                        :error="$errors->first('taxZone.active')">
                        <x-hub::input.toggle wire:model="taxZone.active" />
                    </x-hub::input.group>

                    <x-hub::input.group :label="__('adminhub::inputs.default.label')"
                                        for="active"
                                        :error="$errors->first('taxZone.default')">
                        <x-hub::input.toggle wire:model="taxZone.default" />
                    </x-hub::input.group>
                </div>

                <x-hub::input.group :label="__('adminhub::settings.taxes.tax-zones.zone_type.label')"
                                    for="type"
                                    :error="$errors->first('taxZone.zone_type')"
                                    required>
                    <x-hub::input.select id="type"
                                         wire:model="taxZone.zone_type">
                        <option value="country">
                            {{ __('adminhub::settings.taxes.tax-zones.zone_type.countries') }}
                        </option>

                        <option value="states">
                            {{ __('adminhub::settings.taxes.tax-zones.zone_type.states') }}
                        </option>

                        <option value="postcodes">
                            {{ __('adminhub::settings.taxes.tax-zones.zone_type.postcodes') }}
                        </option>
                    </x-hub::input.select>
                </x-hub::input.group>

                <div>
                    @if ($taxZone->zone_type == 'country')
                        @include('adminhub::partials.forms.tax-zones.country')
                    @endif
                </div>

                <div>
                    @if ($taxZone->zone_type == 'states')
                        @include('adminhub::partials.forms.tax-zones.states')
                    @endif
                </div>

                <div>
                    @if ($taxZone->zone_type == 'postcodes')
                        @include('adminhub::partials.forms.tax-zones.postcode')
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden shadow sm:rounded-md">
        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <div class="space-y-4">
                <header>
                    <h3>
                        {{ __('adminhub::settings.taxes.tax-zones.customer_groups.title') }}
                    </h3>

                    <span class="text-sm text-gray-600">
                        {{ __('adminhub::settings.taxes.tax-zones.customer_groups.instructions') }}
                    </span>
                </header>

                <div class="space-y-4">
                    @foreach ($this->customerGroups as $groupIndex => $customerGroup)
                        <div class="flex items-center justify-between p-3 border rounded"
                             wire:key="cg_{{ $groupIndex }}">
                            <div>
                                {{ $customerGroup['name'] }}
                            </div>

                            <div>
                                <x-hub::input.toggle wire:model="customerGroups.{{ $groupIndex }}.linked" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden shadow sm:rounded-md">
        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <div class="space-y-4">
                <h3>
                    {{ __('adminhub::settings.taxes.tax-zones.tax_rates.title') }}
                </h3>
            </div>

            @if ($errors->has('taxRates'))
                <div class="p-3 space-y-2 text-sm text-red-700 rounded bg-red-50">
                    @foreach ($errors->get('taxRates') as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="space-y-4">
                @foreach ($this->taxRates as $taxRateIndex => $rate)
                    <div wire:key="tax_rate_{{ $taxRateIndex }}"
                         class="p-3 border rounded">
                        <div class="flex justify-end w-full">
                            <x-hub::button size="xs"
                                           theme="gray"
                                           type="button"
                                           wire:click="removeTaxRate({{ $taxRateIndex }})"
                                           :disabled="count($this->taxRates) == 1">
                                <x-hub::icon ref="trash"
                                             class="w-4" />
                            </x-hub::button>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <x-hub::input.group :label="__('adminhub::inputs.name')"
                                                for="name"
                                                required
                                                :error="$errors->first('taxRates.' . $taxRateIndex . '.name')">
                                <x-hub::input.text wire:model="taxRates.{{ $taxRateIndex }}.name" :error="$errors->first('taxRates.' . $taxRateIndex . '.name')" />
                            </x-hub::input.group>

                            <x-hub::input.group :label="__('adminhub::inputs.priority.label')"
                                                for="priority"
                                                required
                                                :error="$errors->first('taxRates.' . $taxRateIndex . '.priority')">
                                <x-hub::input.text type="number"
                                                   wire:model="taxRates.{{ $taxRateIndex }}.priority"
                                                   :error="$errors->first('taxRates.' . $taxRateIndex . '.priority')" />
                            </x-hub::input.group>
                        </div>

                        <div class="mt-4">
                            <div class="grid grid-cols-2 gap-4">
                                @foreach ($rate['amounts'] as $amountIndex => $amount)
                                    <x-hub::input.group for="tr_{{ $taxRateIndex }}_amount_{{ $amountIndex }}"
                                                        wire:key="tr_{{ $taxRateIndex }}_amount_{{ $amountIndex }}"
                                                        :label="$amount['tax_class_name']">
                                        <div class="relative">
                                            <span aria-hidden="true"
                                                  class="absolute inset-y-0 left-0 flex items-center justify-center text-xs font-bold text-gray-500 w-7">
                                                %
                                            </span>

                                            <x-hub::input.text type="number"
                                                               wire:model="taxRates.{{ $taxRateIndex }}.amounts.{{ $amountIndex }}.percentage"
                                                               class="pl-7" />
                                        </div>
                                    </x-hub::input.group>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-hub::button theme="gray"
                           type="button"
                           wire:click="addTaxRate">
                {{ __('adminhub::settings.taxes.tax-zones.tax_rates.create_button') }}
            </x-hub::button>
        </div>
    </div>

    <form wire:submit.prevent="save"
          class="flex justify-between py-3 bg-gray-50">
        <div>
            @if ($taxZone->id)
                <x-hub::button theme="danger"
                               type="button"
                               wire:click="$set('taxZoneToRemove', {{ $taxZone->id }})">
                    {{ __('adminhub::settings.taxes.tax-zones.delete_btn') }}
                </x-hub::button>
            @endif
        </div>

        <x-hub::button type="submit">
            @if ($taxZone->id)
                {{ __('adminhub::settings.taxes.tax-zones.save_btn') }}
            @else
                {{ __('adminhub::settings.taxes.tax-zones.create_btn') }}
            @endif
        </x-hub::button>
    </form>
</div>
