<div x-data="{
    messages: @json($messages),
    level: '{{ $level }}',
    timeout: null,
    remove(message) {
        this.messages.splice(this.messages.indexOf(message), 1)
    },
}"
     x-on:notify.window="level = $event.detail.level; messages.push($event.detail.message)"
     x-init="$watch('messages', () => {
         clearTimeout(timeout)
         timeout = setTimeout(() => messages.shift(), 2000)
     })"
     class="fixed inset-0 z-50 flex flex-col items-center justify-end p-4 space-y-4 pointer-events-none sm:p-6 lg:items-end lg:justify-start">
    <template x-for="(message, messageIndex) in messages"
              :key="messageIndex"
              hidden>
        <div x-transition:enter="ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="w-full max-w-sm p-4 bg-white border border-gray-100 rounded-md shadow-lg pointer-events-auto dark:border-gray-700 dark:bg-gray-800"
             :class="{ 'border-red-600': level === 'error', 'border-green-600': level !== 'error' }">
            <div class="flex gap-2">
                <div class="shrink-0">
                    <span x-show="level === 'error'"
                          class="text-red-600">
                        <x-hub::icon ref="exclamation-circle"
                                     class="w-6 h-6" />
                    </span>

                    <span x-show="level !== 'error'"
                          class="text-green-600">
                        <x-hub::icon ref="check-circle"
                                     class="w-6 h-6" />
                    </span>
                </div>

                <div class="flex-1 leading-5 -mt-[2px]">
                    <strong x-text="message"
                            class="text-sm font-medium text-gray-900 dark:text-white">
                    </strong>
                </div>

                <div class="shrink-0">
                    <button x-on:click="remove(message)"
                            class="p-1 -m-1 text-gray-500 rounded hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400">
                        <x-hub::icon ref="x"
                                     class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
