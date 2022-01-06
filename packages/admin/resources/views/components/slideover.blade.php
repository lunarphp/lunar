<div
  class="fixed inset-0 z-75"
  aria-labelledby="slide-over-title"
  role="dialog"
  aria-modal="true"
  x-data="{
    show: @entangle($attributes->wire('model')),
  }"
  x-on:close.stop="show = false"
  x-show="show"
  style="display: none;"
>
  <div class="absolute inset-0">
    <!--
      Background overlay, show/hide based on slide-over state.

      Entering: "ease-in-out duration-500"
        From: "opacity-0"
        To: "opacity-100"
      Leaving: "ease-in-out duration-500"
        From: "opacity-100"
        To: "opacity-0"
    -->
    <div x-show="show" class="absolute inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" x-on:click="show = false"
      x-transition:enter="ease-in-out duration-500"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="ease-in-out duration-500"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
    ></div>

    <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
      <!--
        Slide-over panel, show/hide based on slide-over state.

        Entering: "transition ease-in-out duration-500 sm:duration-700"
          From: "translate-x-full"
          To: "translate-x-0"
        Leaving: "transition ease-in-out duration-500 sm:duration-700"
          From: "translate-x-0"
          To: "translate-x-full"
      -->
      <div x-show="show" class="w-screen {{ $nested ? 'max-w-xl' : 'max-w-2xl' }}"
        x-transition:enter="transition ease-in-out duration-300 sm:duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 sm:duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
      >
        <div class="flex flex-col h-full py-6 pb-20 overflow-y-scroll bg-white shadow-xl">
          <div class="px-4 sm:px-6">
            <div class="flex items-start justify-between">
              <h2 class="text-lg font-medium text-gray-900" id="slide-over-title">
                {{ $title }}
              </h2>
              <div class="flex items-center ml-3 h-7">
                <button
                  x-on:click="show = false"
                  type="button"
                  class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  <span class="sr-only">Close panel</span>
                  <!-- Heroicon name: outline/x -->
                  <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
          <div class="relative flex-1 px-4 mt-6 sm:px-6">
            {{ $slot }}
          </div>

        </div>
        @if(!empty($footer))
          <div class="absolute bottom-0 right-0 px-10 py-4 text-right bg-white bg-opacity-75 left-10">
            {{ $footer }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
