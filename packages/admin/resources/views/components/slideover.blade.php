<div class="fixed inset-0 z-75 !m-0"
     role="dialog"
     aria-modal="true"
     x-data="{ show: @entangle($attributes->wire('model')) }"
     x-cloak
     x-show="show">
    <div class="absolute inset-0">
        <div x-show="show"
             x-cloak
             class="absolute inset-0 transition-opacity bg-gray-500 bg-opacity-75"
             aria-hidden="true"
             x-on:click="show = false"
             x-transition:enter="ease-in-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-500"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        @if ($form)
            <form wire:submit.prevent="{{ $form }}">
        @endif

        <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div x-show="show"
                 x-trap.noscroll="show"
                 x-cloak
                 x-init="$watch('show', (isShown) => isShown && $focus.first())"
                 class="w-screen {{ $nested ? 'max-w-xl' : 'max-w-2xl' }}"
                 x-transition:enter="transition ease-in-out duration-300 sm:duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 sm:duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                <div class="flex flex-col h-full py-6 pb-20 overflow-y-scroll bg-white shadow-xl">
                    <div class="px-4 sm:px-6">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ $title }}
                            </h2>

                            <div class="flex items-center ml-3 h-7">
                                <button x-on:click="show = false"
                                        type="button"
                                        class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                                    <span class="sr-only">Close panel</span>

                                    <svg class="w-6 h-6"
                                         xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         aria-hidden="true">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex-1 px-4 mt-6 sm:px-6"
                         x-ref="main">
                        {{ $slot }}
                    </div>
                </div>

                @if (!empty($footer))
                    <div class="absolute bottom-0 right-0 px-10 py-4 text-right bg-white bg-opacity-75 left-10">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>

        @if ($form)
            </form>
        @endif
    </div>
</div>
