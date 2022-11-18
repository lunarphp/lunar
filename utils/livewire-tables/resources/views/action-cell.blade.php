<div x-data="{ isActive: false }"
     class="lt-relative">
    <x-l-tables::button size="xs"
                        aria-label="Toggle Menu"
                        x-on:click="isActive = !isActive">
        <span class="sr-only">Row actions</span>

        <svg xmlns="http://www.w3.org/2000/svg"
             fill="none"
             viewBox="0 0 24 24"
             stroke-width="1.5"
             stroke="currentColor"
             class="w-4 h-4">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
        </svg>
    </x-l-tables::button>

    <div x-cloak
         x-transition
         x-show="isActive"
         x-on:click.away="isActive = false"
         x-on:keydown.escape.window="isActive = false"
         role="menu"
         class="lt-absolute lt-right-[calc(100%_+_20px)] lt-z-50 lt-top-0 lt-w-48 lt-text-left lt-origin-top-right lt-bg-white lt-border lt-border-gray-100 lt-rounded-md">
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
