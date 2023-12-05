<form method="POST" wire:submit.prevent="save">
  <div class="space-y-4">
    @include('shipping::partials.forms.shipping-method-top')

    <x-hub::button>Save Method</x-hub::button>
  </div>
</form>
