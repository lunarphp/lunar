<x-hub::slideover-simple target="showMobileMenu">
    <div class="flex items-center justify-between">
        <a href="{{ route('hub.index') }}"
           class="block">
            <img class="w-8 h-8"
                 src="https://markmead.dev/gc-favicon.svg"
                 alt="GetCandy Logo">
        </a>

        <button type="button"
                x-on:click="showMobileMenu = false">
            <x-hub::icon ref="x"
                         class="w-5 h-5 shrink-0" />
        </button>
    </div>

    <div class="mt-8">
        <x-hub::menu handle="sidebar"
                     current="{{ request()->route()->getName() }}">
            <x-hub::menu-list
                    type="main_menu"
                    :sections="$component->sections"
                    :items="$component->items"
                    :active="$component->attributes->get('current')" />
        </x-hub::menu>

        <div class="pt-4 mt-4 border-t border-gray-100">
            @if (Auth::user()->can('settings'))
                <div x-data="{ showSettingsMenu: false }"
                     x-init="showSettingsMenu = {{ Str::contains(request()->url(), 'settings') ? 'true' : 'false' }}">
                    <a href="{{ route('hub.settings') }}"
                       class="justify-between menu-link menu-link--inactive">
                        <div class="flex gap-2">
                            {!! GetCandy\Hub\GetCandyHub::icon('cog', 'w-5 h-5 shrink-0') !!}

                            <span class="text-sm font-medium">
                                {{ __('adminhub::global.settings') }}
                            </span>
                        </div>

                        <button x-on:click.prevent="showSettingsMenu = !showSettingsMenu"
                                class="p-0.5 text-gray-600 bg-white rounded hover:text-gray-700">
                            <span class="block transition shrink-0"
                                  :class="{ '-rotate-180': showSettingsMenu }">
                                <x-hub::icon ref="chevron-down"
                                             class="w-4 h-4"
                                             style="solid" />
                            </span>
                        </button>
                    </a>

                    <div x-cloak
                         x-show="showSettingsMenu"
                         class="mt-4 ml-4">
                        <x-hub::menu handle="settings"
                                     current="{{ request()->route()->getName() }}">
                            <div class="space-y-4">
                                @if (count($component->items))
                                    <div>
                                        <ul class="space-y-2">
                                            @foreach ($component->items as $item)
                                                <li>
                                                    <a href="{{ route($item->route) }}"
                                                       @class([
                                                           'menu-link',
                                                           'menu-link--active' => $item->isActive(
                                                               $component->attributes->get('current')
                                                           ),
                                                           'menu-link--inactive' => !$item->isActive(
                                                               $component->attributes->get('current')
                                                           ),
                                                       ])>
                                                        {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                                                        <span class="text-sm font-medium">
                                                            {{ $item->name }}
                                                        </span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @forelse ($component->sections as $section)
                                    @if (count($section->getItems()))
                                        <div>
                                            <header class="text-sm font-semibold text-gray-600">
                                                {{ $section->name }}
                                            </header>

                                            <ul class="mt-2 space-y-2">
                                                @foreach ($section->getItems() as $item)
                                                    <li>
                                                        <a href="{{ route($item->route) }}"
                                                           @class([
                                                               'flex items-center gap-2 p-2 rounded text-gray-500',
                                                               'bg-blue-50 text-blue-700 hover:text-blue-600' => $item->isActive(
                                                                   $component->attributes->get('current')
                                                               ),
                                                               'hover:bg-blue-50 hover:text-blue-700' => !$item->isActive(
                                                                   $component->attributes->get('current')
                                                               ),
                                                           ])>
                                                            {!! $item->renderIcon('shrink-0 w-5 h-5') !!}

                                                            <span class="text-sm font-medium">
                                                                {{ $item->name }}
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @empty
                                @endforelse
                            </div>
                        </x-hub::menu>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-hub::slideover-simple>
