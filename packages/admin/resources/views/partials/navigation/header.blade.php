<header class="bg-[#063446] flex justify-between items-center px-6 fixed w-full z-50 top-0 py-3">
    <div class="flex items-center space-x-4 text-white">
        <button type="button" @click="toggleMenu()" class="leading-4">
            <x-hub::icon ref="menu" />
        </button>
        <div class="flex items-center">
            <x-hub::branding.logo iconOnly class="w-8 sm:hidden" />
            <x-hub::branding.logo iconOnly class="h-6 hidden sm:block mr-2" />
            Orbital Fasteners
        </div>
    </div>


    <div>
        @include('adminhub::partials.navigation.header-user-dropdown')
    </div>
</header>
