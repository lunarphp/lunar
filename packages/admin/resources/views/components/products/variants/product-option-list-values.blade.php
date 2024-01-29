@props(['items', 'statePath', 'key', 'canAddValues', 'readonly' => false])
<div>
  <div
   class="space-y-2"
   @if(!$readonly)
     x-ref="sortableListValues"
     x-data="{
        items: @js($items)
      }"
     x-init="() => {
          el = $refs.sortableListValues

          el.sortable = Sortable.create(el, {
              group: 'option_values_{{ $key }}',
              draggable: '[x-sortable-item]',
              handle: '[x-sortable-handle]',
              dataIdAttr: 'x-sortable-item',
              animation: 300,
              ghostClass: 'fi-sortable-ghost',
              onEnd: (event) => {
                const rows = items
                console.log(rows)
                const reorderedRow = rows.splice(event.oldIndex, 1)[0]
                items.splice(event.newIndex, 0, reorderedRow)

                rows.forEach(
                  (item, itemIndex) => item.position = itemIndex + 1
                )

                this.items = rows

                $wire.call('updateValuePositions', '{{ $key }}', rows)
              }
          })
        }"
     @endif
  >
    @foreach($items as $itemIndex => $valueItem)
      <div x-sortable-item="{{ $itemIndex }}" wire:key="option_{{ $itemIndex }}_value">
        <div class="flex space-x-2 items-center">
          @if(!$readonly)
          <div
            @class([
              'flex items-center',
              'cursor-grab text-gray-400 hover:text-gray-500' => !$readonly,
              'text-gray-200' => $readonly,
            ])
             x-sortable-handle
          >
            <x-filament::icon alias="lunar::reorder" class="w-5 h-5" />
          </div>
          @endif
          <div
            @class([
              'grow',
              'opacity-50' => !$valueItem['enabled']
            ])
          >
            <x-filament::input.wrapper :valid="!$errors->has($statePath.'.'.$itemIndex.'.value')">
              <x-filament::input
                type="text"
                wire:model="{{ $statePath }}.{{ $itemIndex }}.value"
                :disabled="$readonly"
              />
            </x-filament::input.wrapper>
          </div>
          <div>
            @if(!$readonly)
              <div>
                <button type="button" wire:click.prevent="removeOptionValue('{{ $key }}', '{{ $itemIndex }}')">
                  <x-filament::icon alias="actions::delete-action" class="w-4 h-4 text-red-500" />
                </button>
              </div>
            @else
              <div>
                <x-filament::input.checkbox wire:model.live="{{ $statePath }}.{{ $itemIndex }}.enabled" />
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>
  @if($canAddValues)
    <div class="text-center mt-4">
      <x-filament::button color="gray" size="xs" type="button" wire:click.prevent="addOptionValue('{{ $key }}')">
        {{ __('lunarpanel::components.product-options-list.add-value.label') }}
      </x-filament::button>
      <hr class="-mt-3.5" />
    </div>
  @endif
</div>