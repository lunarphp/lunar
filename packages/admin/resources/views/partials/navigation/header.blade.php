<header class="bg-white shadow flex justify-between items-center px-6 fixed w-full z-50 top-0 py-3">
    <div class="flex items-center space-x-4">
        <button type="button" @click="toggleMenu()" class="leading-4">
            <x-hub::icon ref="menu" />
        </button>
        <div class="flex items-center">
            <x-hub::branding.logo iconOnly class="w-8 sm:hidden" />
            <x-hub::branding.logo iconOnly class="h-6 hidden sm:block mr-2" />
            Orbital Fasteners
        </div>
    </div>


    <div class="flex items-center space-x-4 text-sky-500 leading-4">
        <a href="#" class="flex items-center hover:text-white fill-sky-500 hover:fill-white">
            <svg class="h-6 w-6 fill-inherit" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.503 10.7v2.469c0 .641.345 1.907.896 2.215l4.304 2.398a1.68 1.68 0 0 0 1.653 0l4.305-2.398c.55-.308.895-1.574.895-2.215v-2.47l-5.2 2.9a1.68 1.68 0 0 1-1.653 0l-5.2-2.9zm5.2-8.49L1.446 6.254c-.595.334-.595 1.213 0 1.547l7.257 4.042a1.68 1.68 0 0 0 1.653 0l6.922-3.858v5.193c0 .484.388.88.861.88.474 0 .861-.396.861-.88V7.545a.887.887 0 0 0-.448-.774l-8.196-4.56a1.725 1.725 0 0 0-1.653 0z"></path></svg>
            <div class="pl-2">
                Learn
            </div>
        </a>

        @include('adminhub::partials.navigation.header-user-dropdown')
    </div>
</header>
