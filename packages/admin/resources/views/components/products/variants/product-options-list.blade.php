@props(['items', 'group', 'statePath', 'context' => 'options', 'optionKey' => null])
<div
  class="space-y-4"
  x-ref="sortableOptionList"
  x-data="{
    context: '{{ $context }}',
    items: @js($items)
  }"
  x-init="() => {
      el = $refs.sortableOptionList

      el.sortable = Sortable.create(el, {
          group: 'product_options',
          draggable: '[x-sortable-option-item]',
          handle: '[x-sortable-option-handle]',
          dataIdAttr: 'x-sortable-option-item',
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

            $wire.call('updateOptionPositions', rows)
          }
      })
    }"
>
  @foreach($items as $itemIndex => $item)
    <div wire:key="option_{{ $itemIndex }}" x-sortable-option-item="option_{{ $itemIndex }}">
      <div class="grid grid-cols-2 space-x-4">
        <div>
            <div>
              <x-filament-forms::field-wrapper.label class="ml-7">
                {{ __('lunarpanel::components.product-options-list.name.label') }}
              </x-filament-forms::field-wrapper.label>
              <div class="flex w-full space-x-2 mt-1 items-start">
                <div
                  @class([
                    'flex items-center',
                    'cursor-grab text-gray-400 hover:text-gray-500' => !$item['readonly'] || $context == 'options',
                    ' text-gray-200' => $item['readonly'] && $context == 'values',
                  ])
                  @if(!$item['readonly'] || $context == 'options') x-sortable-option-handle @endif
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
                  <button
                    type="button"
                    class="text-sm font-semibold text-red-500 hover:underline mt-2"
                    wire:click.prevent="removeOption('{{ $itemIndex }}')"
                  >
                    {{ __(
                        !$item['readonly'] ?
                            'lunarpanel::components.product-options-list.delete-option.label' :
                            'lunarpanel::components.product-options-list.remove-shared-option.label'
                    ) }}
                  </button>
                </div>
              </div>
            </div>
        </div>
        <div class="space-y-1">
          <x-filament-forms::field-wrapper.label>
            {{ __('lunarpanel::components.product-options-list.values.label') }}
          </x-filament-forms::field-wrapper.label>
          <div wire:key="option_values_{{ $itemIndex }}">
            <x-lunarpanel::products.variants.product-option-list-values
              :items="$item['option_values']"
              :key="$itemIndex"
              state-path="configuredOptions.{{ $itemIndex }}.option_values"
              :can-add-values="!$item['readonly']"
              :readonly="$item['readonly']"
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