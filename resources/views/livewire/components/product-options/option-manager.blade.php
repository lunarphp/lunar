<div>
  @livewire('hub.components.product-options.option-value-create-modal')

  <div class="space-y-4">
    @foreach($options as $key => $option)
      <div class="overflow-hidden border rounded shadow-sm">
        <header class="flex items-center justify-between px-4 py-2 text-xs font-medium text-gray-500 uppercase border-b bg-gray-50">
          {{ $option->translate('name') }}
          <div class="flex items-center space-x-4">
              <button wire:click.prevent="$set('selectedOption', '{{ $option->id }}')" class="px-2 py-1 text-xs text-green-600 border border-green-500 rounded hover:bg-green-50">
              {{ __('adminhub::components.products.option-manager.add_btn') }}
             </button>
             <button type="button" wire:click.prevent="toggle('{{ $option->id }}')" class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100">
              {{ __('adminhub::components.products.option-manager.toggle_btn') }}
             </button>
            <button class="hover:text-red-600" type="button" wire:click.prevent="removeOption('{{ $key }}')">
              <x-hub::icon ref="trash" class="w-4 h-4" />
            </button>
          </div>
        </header>
        <div class="p-4 space-y-2 bg-white">
          <div class="flex flex-wrap items-center gap-2">
              @foreach($option->values as $value)
                <label class="relative block" wire:key="option_{{ $value->id }}">
                  <input type="checkbox" class="absolute mt-2 left-2 peer" wire:model.debounce.100ms="selectedValues" value="{{ $value->id }}">
                  <span class="items-center inline-block px-2 py-1 pl-6 space-x-1 text-sm border rounded shadow-sm cursor-pointer hover:bg-gray-100 bg-gray-50 peer-checked:bg-blue-200 peer-checked:text-blue-900 peer-checked:border-blue-500">{{ $value->name->en }}</span>
                </label>
              @endforeach
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>
