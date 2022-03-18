<div class="space-y-4">
  @if(!$this->intents->count())
    <x-hub::alert level="danger">
      {{ __('adminhub::components.orders.capture.no_intents') }}
    </x-hub::alert>
  @else
    <x-hub::input.group for="amount" :label="__('adminhub::inputs.transaction.label')" required :error="$errors->first('transaction')">
      <x-hub::input.select wire:model="transaction">
        <option value>{{ __('adminhub::components.orders.capture.select_transaction') }}</option>
        @foreach($this->intents as $intent)
          <option value="{{ $intent->id }}">
            {{ $intent->amount->formatted }} - {{ $intent->driver }} // {{ $intent->reference }}
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

    @if($this->transactionModel->amount->value > ($amount * 100))
      <x-hub::alert level="danger">
        You're about to capture an amount less than the total transaction value.
      </x-hub::alert>
    @endif

    <x-hub::input.group
      for="confirm"
      :label="__('adminhub::inputs.confirm.label')"
      :instructions="__('adminhub::components.orders.capture.confirm_message', [
        'confirm' => __('adminhub::components.orders.capture.confirm_text')
      ])"
    >
      <x-hub::input.toggle wire:model="confirmed" />
    </x-hub::input.group>

    <x-hub::button
      :disabled="!$confirmed"
      wire:click.prevent="capture"
      type="button"
    >
      <div wire:loading wire:target="capture">
        <x-hub::icon ref="refresh" class="inline-block w-4 rotate-180 animate-spin" />
      </div>
      <div wire:loading.remove wire:target="capture">
        {{ __('adminhub::components.orders.capture.capture_btn') }}
      </div>
    </x-hub::button>

    @if($this->captureError)
      <x-hub::alert level="danger">
        {{ $this->captureError }}
      </x-hub::alert>
    @endif
  @endif
</div>