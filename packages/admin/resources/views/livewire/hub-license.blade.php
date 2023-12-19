<div>
@if($unlicensed)
  <!-- This example requires Tailwind CSS v2.0+ -->
  <div class="fixed inset-x-0 bottom-0 pb-2 sm:pb-5">
    <div class="px-2 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="p-2 @if($unlicensed) bg-red-600 @endif rounded-lg shadow-lg sm:p-1">
        <div class="flex flex-wrap items-center justify-between">
          <div class="flex items-center flex-1 w-0">
            <p class="ml-3 text-sm text-white truncate">
                @if($unlicensed)
                  {{ __('adminhub::licensing.banner.unlicensed.text') }}
                @endif
            </p>
          </div>
          <div class="flex-shrink-0 order-3 w-full mt-2 sm:order-2 sm:mt-0 sm:w-auto">
            <a href="#" class="flex items-center justify-center px-4 py-2 text-xs font-medium text-white bg-red-800 border border-transparent rounded-md shadow-sm hover:bg-red-50">
              @if($unlicensed)
                {{ __('adminhub::licensing.banner.unlicensed.btn_text') }}
              @endif
            </a>
          </div>
          {{-- <div class="flex-shrink-0 order-2 sm:order-3 sm:ml-2">
            <button type="button" class="flex p-2 -mr-1 rounded-md hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-white">
              <span class="sr-only">Dismiss</span>
              <!-- Heroicon name: outline/x -->
              <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div> --}}
        </div>
      </div>
    </div>
  </div>
@endif

</div>
