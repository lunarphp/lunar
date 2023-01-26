<div
  @class([
    'relative flex',
    'justify-center' => !$attributes->get('left'),
    'justify-left' => $attributes->get('left')
  ])
  x-data="{
    showToolTip: false,
    tooltipText: @js($text)
  }"
   @mouseenter="showToolTip = true"
   @mouseleave="showToolTip = false"
>
  <div class="absolute z-50 p-2 -mt-10 text-xs text-white bg-gray-900 rounded-md whitespace-nowrap" x-cloak x-show="showToolTip" x-html="tooltipText"></div>
  {{ $slot }}
</div>
