<header class="bg-white flex items-center px-8">
    <div class="flex items-center">
        {{-- <x-hub::branding.logo x-cloak iconOnly class="w-6" /> --}}
        <strong class="text-xl">Lunar</strong>
    </div>
    <div class="grow px-24 flex items-center space-x-2">
        <x-hub::input.text placeholder="Search the hub" />
        <x-hub::icon ref="search" style="solid" class="w-6" />
    </div>

    <div>
        @include('adminhub::partials.navigation.header-user-dropdown')
    </div>
</header>
