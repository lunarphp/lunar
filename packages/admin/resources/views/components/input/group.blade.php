<div>
  <label for="{{ $for }}" class="flex items-center text-sm font-medium text-gray-700">
    <span class="block">{{ $labelPrefix ?? null }}</span>
    <span class="block">{{ $label }}@if($required)<sup class="text-xs text-red-600">&#42;</sup>@endif</span>
  </label>
  <div class="relative mt-1">
    {{ $slot }}
    @if($error)
      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <!-- Heroicon name: solid/exclamation-circle -->
        <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      </div>
    @endif
  </div>
  @if($instructions)
    <p class="mt-2 text-sm text-gray-500">{{ $instructions }}</p>
  @endif
  @if($error)
    <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
  @endif
  @if(count($errors))
    <div class="space-y-1">
      @foreach($errors as $error)
        @if(is_array($error))
          @foreach($error as $text)
            <p class="text-sm text-red-600">{{ $text }}</p>
          @endforeach
        @else
          <p class="text-sm text-red-600">{{ $error }}</p>
        @endif
      @endforeach
    </div>
  @endif
</div>
