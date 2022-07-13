<div class="flex-col space-y-4">
  <div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
      <div class="space-y-4">


        <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('taxZone.name')">
          <x-hub::input.text wire:model="taxZone.name" name="name" id="name" :error="$errors->first('taxZone.name')" />
        </x-hub::input.group>


        <x-hub::input.group label="Type" for="type"  :error="$errors->first('taxZone.zone_type')">
          <x-hub::input.select id="type" wire:model="taxZone.zone_type">
            <option value="country">Limit to Countries</option>
            <option value="states">Limit to States / Provinces</option>
            <option value="postcodes">Limit to list of Postcodes</option>
          </x-hub::input.select>
        </x-hub::input.group>


        <div>
        @if($taxZone->zone_type == 'country')
          @include('adminhub::partials.forms.tax-zones.country')
        @endif
        </div>

        <div>
        @if($taxZone->zone_type == 'states')
          @include('adminhub::partials.forms.tax-zones.states')
        @endif
        </div>

        @if($taxZone->zone_type == 'postcodes')
          @include('adminhub::partials.forms.tax-zones.postcode')
        @endif
      </div>
    </div>


  </div>

  <div>
    @if($this->taxRate)
      <x-hub::slideover wire:model="rateId" form="saveAddress">
        <div class="space-y-4">
          <x-hub::input.group :label="__('adminhub::inputs.name')" for="taxRateName" :error="$errors->first('taxRate.name')">
            <x-hub::input.text wire:model="taxRate.name" name="taxRateName" id="taxRateName" :error="$errors->first('taxRate.name')" />
          </x-hub::input.group>

          <h3>Tax Rates</h3>

          @foreach($this->taxRateAmounts as $index => $taxRateAmount)
            <div class="grid grid-cols-2 gap-4 items-center">
              <div>
                <x-hub::input.group label="Tax Class" for="taxRateAmountClass">
                  {{ $taxRateAmount['name'] }}
                </x-hub::input.group>
              </div>
              <x-hub::input.group label="Percentage" for="taxRateAmountPercentage" :error="$errors->first('taxRateAmount.name')">
                <x-hub::input.text wire:model="taxRateAmounts.{{ $index }}.percentage" name="taxRateAmountName" id="taxRateAmountName" :error="$errors->first('taxRateAmount.name')" />
              </x-hub::input.group>
            </div>
          @endforeach
        </div>
      </x-hub::slideover>
   @endif
  </div>


  <div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
      <div class="space-y-4">
        <h3>Tax Rates</h3>
      </div>

      @foreach($taxZone->taxRates as $rate)
        <div class="border rounded py-2 px-4 flex items-center justify-between" wire:key="tax_rate_{{ $rate->id }}">
          <div>
            {{ $rate->name }}
          </div>

          <div>
            <x-hub::button size="sm" wire:click="$set('rateId', {{ $rate->id }})">Edit</x-hub::button>
          </div>
        </div>
      @endforeach


    </div>
  </div>

  <form wire:submit.prevent="save" class="py-3 justify-between bg-gray-50 flex">
    <div>
      @if($taxZone->id)
        <x-hub::button theme="danger" type="button"  wire:click="$set('showDeleteConfirm', true)">
          Delete tax zone
        </x-hub::button>
      @endif
    </div>
    <x-hub::button type="submit">
      @if($taxZone->id)
        Save tax zone
      @else
        Create tax zone
      @endif
    </x-hub::button>
  </form>
</div>
