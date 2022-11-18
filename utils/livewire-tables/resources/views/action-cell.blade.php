<div x-data="{ isActive: false }"
     class="lt-relative">
    <x-l-tables::button size="xs"
                        aria-label="Toggle Menu"
                        x-on:click="isActive = !isActive">
        <span :class="{ 'lt-rotate-45': isActive }"
              class="lt-transition -lt-ml-1">
            <svg xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 20 20"
                 fill="currentColor"
                 class="lt-w-4 lt-h-4">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd" />
            </svg>
        </span>

        <span>
            Actions
        </span>
    </x-l-tables::button>

    <div x-cloak
         x-transition
         x-show="isActive"
         x-on:click.away="isActive = false"
         x-on:keydown.escape.window="isActive = false"
         role="menu"
         class="lt-absolute lt-right-[calc(100%_+_1.5rem)] lt-z-50 lt-top-0 lt-w-48 lt-text-left lt-origin-top-right lt-bg-white lt-border lt-border-gray-100 lt-rounded-lg lt-shadow-sm">
        <div class="p-2">
            @foreach ($this->actions as $actionIndex => $action)
                @php
                    $action = $action->record($record);
                @endphp

                {{ $action }}
            @endforeach
        </div>
    </div>
</div>
