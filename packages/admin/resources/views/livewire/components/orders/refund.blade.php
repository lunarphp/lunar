<div class="space-y-4">
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
    <x-hub::input.text wire:model="confirmText" />
  </x-hub::input.group>


  <x-hub::button :disabled="!$this->isConfirmed" wire:click.prevent="refund" type="button">
    Send Refund
  </x-hub::button>
</div>