@foreach($this->getGroups($type) as $group)
  <div>
    <div class="grid grid-cols-2 gap-6 p-3 text-xs font-bold text-gray-500 uppercase bg-gray-50">
      <h3>{{ $group->translate('name') }}</h3>
      <h3>{{ $group->translate('name') }}</h3>
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
          <div class="text-sm text-gray-500">There are no attributes to select in this group</div>
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
{{-- <div class="lg:grid lg:grid-cols-2 lg:gap-8">
  <div>
    <div class="space-y-4">
      @foreach($this->productAttributeGroups as $group)
        <div>
          <header>
            <h3>{{ $group->translate('name') }} ({{ $this->getProductAttributesForGroup($group->id)->count() }})</h3>
          </header>
          <div class="mt-4 space-y-2">
            @forelse($this->getProductAttributesForGroup($group->id) as $attribute)
              <div class="flex items-center justify-between p-3 text-sm bg-white border rounded shadow-sm">
                <div>{{ $attribute->translate('name') }}</div>
                <div>
                  <x-hub::button type="button" theme="gray" size="xs" wire:click="addAttribute('{{ $attribute->id }}')">
                    {{ __('adminhub::global.add') }}
                  </x-hub::button>
                </div>
              </div>
            @empty
              <div class="text-sm text-gray-500">There are no attributes to select in this group</div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
    {{ $this->availableProductAttributes->links() }}
  </div>

  <div>
    <div class="space-y-2" :class="view == 'selected' ? 'block' : 'hidden lg:block'">
      <h3 class="hidden lg:block">
        {{ __('adminhub::partials.product-type.selected_title', [
          'count' => $this->selectedProductAttributes->count()
        ]) }}
      </h3>

      @foreach($this->productAttributeGroups as $group)
        <div>
          <header>
            <h3>{{ $group->translate('name') }}</h3>
          </header>
        </div>

        @foreach($this->getSelectedProductAttributesForGroup($group->id) as $attribute)
        <div class="flex items-center justify-between p-3 text-sm bg-white rounded shadow">
                <div class="flex items-center">
                  @if($attribute->system)
                    <x-hub::icon ref="lock-closed" class="w-4 mr-2 text-yellow-700" />
                  @endif
                  {{ $attribute->translate('name') }}
                </div>
                <div>
                  @if(!$attribute->system)
                  <x-hub::button type="button" theme="gray" size="xs" wire:click="removeAttribute('{{ $attribute->id }}')">
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
      @endforeach
    </div>
  </div>
</div> --}}