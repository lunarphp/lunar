<header class="hidden bg-white dark:bg-gray-900 lg:block">
    <div class="h-16 px-4 mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
        <div class="lg:justify-end lg:flex lg:items-center">
            <button x-data
                    x-on:click="darkMode = !darkMode">
                Dark Mode
            </button>

            @include('adminhub::partials.navigation.header-user-dropdown')
        </div>
    </div>
</header>
