@props(['items', 'statePath', 'key', 'canAddValues', 'readonly' => false])
<div>
  <div
   class="space-y-2"
   x-sortable
   x-data="{
      valueItems: @js($items)
    }"
   x-init="() => {
        el = $refs.sortable

        el.sortable = Sortable.create(el, {
            group: 'option_values_{{ $key }}',
            draggable: '[x-sortable-item]',
            handle: '[x-sortable-handle]',
            dataIdAttr: 'x-sortable-item',
            animation: 300,
            ghostClass: 'fi-sortable-ghost',
            onEnd: (event) => {
              const rows = Alpine.raw(items)
              const reorderedRow = rows.splice(event.oldIndex, 1)[0]
              items.splice(event.newIndex, 0, reorderedRow)

              rows.forEach(
                (item, itemIndex) => item.position = itemIndex + 1
              )

              this.items = rows
            }
        })
      }"
  >
    @foreach($items as $itemIndex => $valueItem)
      <div x-sortable-item="option_{{ $itemIndex }}_value" wire:key="option_{{ $itemIndex }}_value">
        <div class="flex space-x-2 items-center">
          <div
            @class([
              'flex items-center',
              'cursor-grab text-gray-400 hover:text-gray-500' => !$readonly,
              'text-gray-200' => $readonly,
            ])
            @if(!$readonly) x-sortable-handle @endif
          >
            <x-filament::icon alias="lunar::reorder" class="w-5 h-5" />
          </div>
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
                <x-lunarpanel::forms.toggle :statePath="$statePath . '.'.$itemIndex.'.enabled'" />
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