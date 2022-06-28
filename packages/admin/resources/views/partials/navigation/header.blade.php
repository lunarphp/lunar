<header class="hidden bg-white lg:block">
    <div class="h-16 px-4 mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
        <div class="lg:justify-between lg:flex lg:items-center">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd"
                          d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                          clip-rule="evenodd" />
                </svg>

                <span class="text-sm font-medium"> Back </span>
            </a>

            @include('adminhub::partials.navigation.header-user-dropdown')
        </div>
    </div>
</header>
