<div
  x-data="{ showTranslations: {{ $expanded ? 'true' : 'false' }} }"
  >
    @if($languages->count())
    <div class="absolute top-0 left-0 -ml-10 -mt-7">
        <button
          @click.prevent="showTranslations = !showTranslations"
          class="inline-flex items-center px-2 py-1 text-sm font-medium text-gray-700 uppercase bg-white border rounded shadow-sm hover:bg-gray-100"
          type="button"
        >
          <x-hub::icon ref="translate" class="w-3 h-4" />
        </button>
    </div>
    @endif
    <div class="flex items-center">
      <div  x-show="showTranslations" x-cloak>
        <span class="p-2 pr-3 text-xs text-gray-600 uppercase bg-gray-100 border rounded-l border-r-none">
          {{ $default->code }}
        </span>
      </div>
      <div class="w-full">
        {{ $slot }}
      </div>
    </div>
    @if($languages->count())
    <div class="pb-2 mt-2 space-y-2" x-show="showTranslations" x-cloak>
      @foreach($languages as $language)
        @if(${"{$language->code}"} ?? null)
          <div class="relative flex items-center" wire:key="language-{{ $language->id }}">
            <div x-show="showTranslations">
              <span class="p-2 pr-3 text-xs text-gray-600 uppercase bg-gray-100 border rounded-l border-r-none">
                {{ $language->code }}
              </span>
            </div>
            <div class="w-full">
              {{ ${"{$language->code}"} ?? null }}
            </div>
          </div>
        @endif
      @endforeach
    </div>
    @endif
</div>
