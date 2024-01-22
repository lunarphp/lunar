@props(['items', 'group', 'statePath', 'context' => 'options', 'optionKey' => null])
<div
  class="space-y-4"
  x-ref="sortable"
  x-data="{
    context: '{{ $context }}',
    configuredOptions: @entangle($statePath).live
  }"
  x-init="() => {
      el = $refs.sortable

      el.sortable = Sortable.create(el, {
          group: '{{ $group }}',
          draggable: '[x-sortable-item]',
          handle: '[x-sortable-handle]',
          dataIdAttr: 'x-sortable-item',
          animation: 300,
          ghostClass: 'fi-sortable-ghost',
          onEnd: (event) => {
            const rows = configuredOptions

            const reorderedRow = rows.splice(event.oldIndex, 1)[0]

            rows.splice(event.newIndex, 0, reorderedRow)

            rows.forEach(
              (row, rowIndex) => rows[rowIndex].position = rowIndex + 1
            )

            configuredOptions = rows
          }
      })
    }"
>
  @foreach($items as $itemIndex => $item)
    <div wire:key="option_{{ $item['key'] }}" x-sortable-item="{{ $item['key'] }}">
      <div class="flex space-x-2 items-center">
        <div @class([
                'flex items-center',
                'cursor-grab text-gray-400 hover:text-gray-500' => !$item['readonly'] || $context == 'options',
                ' text-gray-200' => $item['readonly'] && $context == 'values',
            ]) @if(!$item['readonly'] || $context == 'options') x-sortable-handle @endif >
          <x-filament::icon alias="lunar::reorder" class="w-5 h-5" />
        </div>
        <div class="grow">
          <x-filament::input.wrapper :valid="!$errors->has($statePath.'.'.$itemIndex.'.value')">
            <x-filament::input
                    type="text"
                    error="'dawda'"
                    wire:model="{{ $statePath }}.{{ $itemIndex }}.value"
                    :disabled="$item['readonly']"
            />
          </x-filament::input.wrapper>
        </div>
        <div>
          <button type="button" wire:click.prevent="removeOptionValue('{{ $optionKey }}', '{{ $itemIndex }}')">
            <x-filament::icon alias="actions::delete-action" class="w-4 h-4 text-red-500" />
          </button>
        </div>
      </div>
      @if(!empty($item['option_values']))
        <div class="space-y-2 mt-4 mx-8 ml-8">
          <x-lunarpanel::products.variants.product-options-list
                  :items="$item['option_values']"
                  context="values"
                  group="product_option_values_{{ $item['key'] }}"
                  :optionKey="$itemIndex"
                  state-path="configuredOptions.{{ $itemIndex }}.option_values"
          />
        </div>
      @endif
    </div>
  @endforeach

  @if($context == 'options')
    <x-filament::link href="#" wire:click.prevent="addRestrictedOption">
      {{ __('lunarpanel::components.product-options-list.add-option.label') }}
    </x-filament::link>
  @endif

  @if($context == 'values' && !$item['readonly'])
    <x-filament::link href="#" wire:click.prevent="addOptionValue('{{ $optionKey }}')">
      {{ __('lunarpanel::components.product-options-list.add-value.label') }}
    </x-filament::link>
  @endif

</div>