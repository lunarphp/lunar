<div class="space-y-4">
  @if(!$this->charges->count())
    <x-hub::alert level="danger">
      {{ __('adminhub::components.orders.refund.no_charges') }}
    </x-hub::alert>
  @else
    <x-hub::input.group for="amount" :label="__('adminhub::inputs.transaction.label')" required :error="$errors->first('transaction')">
      <x-hub::input.select wire:model="transaction">
        <option value>{{ __('adminhub::components.orders.refund.select_transaction') }}</option>
        @foreach($this->charges as $charge)
          <option value="{{ $charge->id }}">
            {{ $charge->amount->formatted }} - {{ $charge->driver }} // {{ $charge->reference }}
          </option>
        @endforeach
      </x-hub::input.select>
    </x-hub::input.group>

    <x-hub::input.group for="amount" :label="__('adminhub::inputs.amount.label')" required :error="$errors->first('amount')">
      <x-hub::input.price
        wire:model="amount"
        :symbol="$order->currency->format"
        :currencyCode="$order->currency->code"
        required
      />
    </x-hub::input.group>

    <x-hub::input.group for="notes" :label="__('adminhub::inputs.notes.label')">
      <x-hub::input.textarea wire:model="notes" />
    </x-hub::input.group>

    <x-hub::input.group
      for="confirm"
      :label="__('adminhub::inputs.confirm.label')"
      :instructions="__('adminhub::components.orders.refund.confirm_message', [
        'confirm' => __('adminhub::components.orders.refund.confirm_text')
      ])"
    >
      <x-hub::input.toggle wire:model="confirmed" />
    </x-hub::input.group>

    <x-hub::button
      :disabled="!$confirmed"
      wire:click.prevent="refund"
      type="button"
    >
      <div wire:loading wire:target="refund">
        <x-hub::icon ref="refresh" class="inline-block w-4 rotate-180 animate-spin" />
      </div>
      <div wire:loading.remove wire:target="refund">
        {{ __('adminhub::components.orders.refund.refund_btn') }}
      </div>
    </x-hub::button>

    @if($this->refundError)
      <x-hub::alert level="danger">
        {{ $this->refundError }}
      </x-hub::alert>
    @endif
  @endif
</div>