<div x-data="{ isActive: false }"
     class="lt-relative">
    <x-l-tables::button size="xs"
                      aria-label="Toggle Menu"
                      x-on:click="isActive = !isActive">
        <span>
            Actions
        </span>

        <svg xmlns="http://www.w3.org/2000/svg"
             class="lt-w-4 lt-h-4"
             viewBox="0 0 20 20"
             fill="currentColor">
            <path fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
        </svg>
    </x-l-tables::button>

    <div x-cloak
         x-transition
         x-show="isActive"
         x-on:click.away="isActive = false"
         x-on:keydown.escape.window="isActive = false"
         role="menu"
         class="lt-absolute lt-right-0 lt-z-50 lt-w-48 lt-mt-2 lt-text-left lt-origin-top-right lt-bg-white lt-border lt-border-gray-100 lt-rounded-lg">
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
