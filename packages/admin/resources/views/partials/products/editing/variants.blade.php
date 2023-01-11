<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('adminhub::partials.products.variants.heading') }}
        </h3>
        <p class="text-sm text-gray-500">{{ __('adminhub::partials.products.variants.strapline') }}</p>
      </div>
      <div class="flex items-center gap-2">
        @if($variantsEnabled)
            <x-hub::button x-on:click="Livewire.emit('toggleOptionSelector')" theme="gray" size="sm" type="button">
                Select option
            </x-hub::button>
        @endif
        <x-hub::input.toggle wire:model="variantsEnabled" />
      </div>
    </header>
      @if($this->getVariantsCount() <= 1)
        <div>
          @include('adminhub::partials.attributes', [
            'attributeGroups' => $this->variantAttributeGroups,
            'mapping' => 'variantAttributes',
            'inline' => true,
          ])
        </div>
      @endif
      @if(!$variantsEnabled && $this->getVariantsCount() > 1)
        <x-hub::alert level="danger">
            {{ __('adminhub::partials.products.variants.removal_message') }}
        </x-hub::alert>
      @endif
      @if($variantsEnabled)
        @include('adminhub::partials.products.editing.options', [
          'openPanel' => $this->getVariantsCount() <= 1
        ])
        
        @include('adminhub::partials.products.variants.manager')
        
        @if($this->getVariantsCount() > 1)
          @livewire('hub.components.products.variants.table', [
            'product' => $this->product,
          ])
        @endif
      @endif
  </div>
</div>
