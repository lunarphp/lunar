<div class="lg:hidden">
    <div class="flex items-center justify-between h-16 px-4 bg-white border-b border-gray-100 sm:px-6 lg:px-8">
        <a href="{{ route('hub.index') }}"
           class="block">
            <img class="w-8 h-8"
                 src="https://markmead.dev/gc-favicon.svg"
                 alt="GetCandy Logo">
        </a>

        <div class="flex items-center gap-4">
            <button type="button"
                    x-on:click="showMobileMenu = true">
                <svg class="w-6 h-6"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="2"
                     stroke="currentColor"
                     aria-hidden="true">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <span class="w-px h-8 bg-gray-100"
                  aria-hidden="true"></span>

            @include('adminhub::partials.navigation.header-user-dropdown')
        </div>
    </div>
</div>
