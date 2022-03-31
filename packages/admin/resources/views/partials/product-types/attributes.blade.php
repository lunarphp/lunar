@foreach($this->getGroups($type) as $group)
  <div>
    <div class="grid grid-cols-2 gap-6 p-3 text-xs font-bold text-gray-500 uppercase bg-gray-50">
      <div class="flex justify-between">
        <h3>{{ $group->translate('name') }}</h3>
        <button type="button" wire:click="selectAll('{{ $group->id }}', '{{ $type }}')" class="hover:text-gray-600">{{ __('adminhub::components.products.index.select_all_btn') }}</button>
      </div>
      <div class="flex justify-between">
        <h3>{{ $group->translate('name') }}</h3>
        <button type="button" wire:click="deselectAll('{{ $group->id }}', '{{ $type }}')" class="hover:text-gray-600">{{ __('adminhub::components.products.index.deselect_all_btn') }}</button>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-6">
      <div class="py-3 space-y-2">
        @forelse($this->getAttributesForGroup($group->id, $type) as $attribute)
          <div wire:key="attribute_{{ $attribute->id }}" class="flex items-center justify-between p-3 text-sm bg-white border rounded shadow-sm">
            <div>{{ $attribute->translate('name') }}</div>
            <div>
              <x-hub::button
                type="button"
                theme="gray"
                size="xs"
                wire:click="addAttribute('{{ $attribute->id }}', '{{ $type }}')"
              >
                {{ __('adminhub::global.add') }}
              </x-hub::button>
            </div>
          </div>
        @empty
          <div class="text-sm text-gray-500">{{ __('adminhub::catalogue.product-types.attribute.no_attributes') }}</div>
        @endforelse
      </div>

      <div class="py-3 space-y-2">
        @foreach($this->getSelectedAttributes($group->id, $type) as $attribute)
        <div wire:key="selected_{{ $attribute->id }}" class="flex items-center justify-between p-3 text-sm bg-white border rounded shadow-sm">
                <div class="flex items-center">
                  @if($attribute->system)
                    <x-hub::icon ref="lock-closed" class="w-4 mr-2 text-yellow-700" />
                  @endif
                  {{ $attribute->translate('name') }}
                </div>
                <div>
                  @if(!$attribute->system)
                  <x-hub::button type="button" theme="gray" size="xs" wire:click="removeAttribute('{{ $attribute->id }}', '{{ $type }}')">
                    {{ __('adminhub::global.remove') }}
                  </x-hub::button>
                  @else
                    <span class="text-xs text-gray-500">
                      {{ __('adminhub::partials.product-type.attribute_system_required') }}
                    </span>
                  @endif
                </div>
              </div>
        @endforeach
      </div>
    </div>
  </div>
@endforeach