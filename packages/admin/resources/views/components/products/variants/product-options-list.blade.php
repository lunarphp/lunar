@props(['items', 'group', 'statePath', 'context' => 'options', 'optionKey' => null])
<div
  class="space-y-4"
  x-ref="sortable"
  x-data="{
    context: '{{ $context }}',
    items: @entangle($statePath).live
  }"
  x-init="() => {
      el = $refs.sortable

      el.sortable = Sortable.create(el, {
          group: 'product_options',
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
  @foreach($items as $itemIndex => $item)
    <div wire:key="option_{{ $itemIndex }}" x-sortable-item="option_{{ $itemIndex }}">
      <div class="grid grid-cols-2 space-x-4">
        <div>
            <div>
              <x-filament-forms::field-wrapper.label class="ml-7">
                Name
              </x-filament-forms::field-wrapper.label>
              <div class="flex w-full space-x-2 mt-1">
                <div
                  @class([
                    'flex items-center',
                    'cursor-grab text-gray-400 hover:text-gray-500' => !$item['readonly'] || $context == 'options',
                    ' text-gray-200' => $item['readonly'] && $context == 'values',
                  ])
                  @if(!$item['readonly'] || $context == 'options') x-sortable-handle @endif
                >
                  <x-filament::icon alias="lunar::reorder" class="w-5 h-5" />
                </div>
                <div class="grow">
                  <x-filament::input.wrapper :valid="!$errors->has($statePath.'.'.$itemIndex.'.value')">
                    <x-filament::input
                            type="text"
                            wire:model="{{ $statePath }}.{{ $itemIndex }}.value"
                            :disabled="$item['readonly']"
                    />
                  </x-filament::input.wrapper>

                </div>
              </div>
            </div>
        </div>
        <div class="space-y-1">
          <x-filament-forms::field-wrapper.label>
            Values
          </x-filament-forms::field-wrapper.label>
          <div>
            <x-lunarpanel::products.variants.product-option-list-values
              :items="$item['option_values']"
              :key="$itemIndex"
              state-path="configuredOptions.{{ $itemIndex }}.option_values"
            />
          </div>
        </div>
      </div>
    </div>
  @endforeach

  <x-filament::button color="gray" size="sm" type="button" wire:click.prevent="addRestrictedOption">
    {{ __('lunarpanel::components.product-options-list.add-option.label') }}
  </x-filament::button>
</div>