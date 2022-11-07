<div>
  <div class="relative rounded-md shadow-sm">
    @if ($symbol)
      <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <span class="text-gray-500 sm:text-sm">
          {{ str_replace('{value}', '', $symbol) }}
        </span>
      </div>
    @endif
    
    <x-hub::input.text 
      {{ $attributes->class(['border-red-400' => !!$error, 'pl-7' => $symbol]) }} 
      class="pr-12" 
      type="number" 
      step="any" />

    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
      <span class="text-gray-500 sm:text-sm">
        {{ $currencyCode }}
      </span>
    </div>
  </div>
</div>
