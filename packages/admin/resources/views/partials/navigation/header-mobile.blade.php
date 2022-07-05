<header class="lg:hidden">
    <div class="flex items-center justify-between h-16 px-4 bg-white border-b border-gray-100 sm:px-6 lg:px-8">
        <a href="{{ route('hub.index') }}"
           class="block">
            <img class="w-8 h-8"
                 src="https://markmead.dev/gc-favicon.svg"
                 alt="GetCandy Logo">
        </a>

        <div class="flex items-center gap-4">
            <button x-on:click="showMobileMenu = true">
                <x-hub::icon ref="menu"
                             class="w-5 h-5 shrink-0" />
            </button>

            <span class="w-px h-8 bg-gray-100"
                  aria-hidden="true"></span>

            @include('adminhub::partials.navigation.header-user-dropdown')
        </div>
    </div>
</header>
