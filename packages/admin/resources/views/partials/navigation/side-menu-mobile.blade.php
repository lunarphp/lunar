<x-hub::slideover-simple target="showMobileMenu">
    <div class="flex items-center justify-between">
        <a href="{{ route('hub.index') }}"
           class="block">
            <x-hub::branding.logo iconOnly />
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
            <ul class="px-2 space-y-2">
                @foreach ($component->items as $item)
                    <li class="relative">
                        <a href="{{ route($item->route) }}"
                           @class([
                               'menu-link group',
                               'menu-link--active' => $item->isActive(
                                   $component->attributes->get('current')
                               ),
                               'menu-link--inactive !text-gray-700' => !$item->isActive(
                                   $component->attributes->get('current')
                               ),
                           ])>
                            <span>
                                {!! $item->renderIcon('w-5 h-5') !!}
                            </span>

                            <span class="text-sm font-medium">
                                {{ $item->name }}
                            </span>
                        </a>
                    </li>
                @endforeach

                @foreach ($component->groups as $group)
                    <li class="relative">
                        <header class="text-sm font-semibold text-gray-600">
                            {{ $group->name }}
                        </header>

                        @if (count($group->getItems()))
                            <ul class="mt-1 space-y-1">
                                @foreach ($group->getItems() as $item)
                                    <li @class(['relative', 'pb-4' => $loop->last])>
                                        <a href="{{ route($item->route) }}"
                                           @class([
                                               'menu-link group',
                                               'menu-link--active' => $item->isActive(
                                                   $component->attributes->get('current')
                                               ),
                                               'menu-link--inactive !text-gray-700' => !$item->isActive(
                                                   $component->attributes->get('current')
                                               ),
                                           ])>
                                            <span>
                                                {!! $item->renderIcon('w-5 h-5') !!}
                                            </span>

                                            <span class="text-sm font-medium">
                                                {{ $item->name }}
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if (count($group->getSections()))
                            <ul x-cloak
                                class="mt-1 space-y-1">
                                @foreach ($group->getSections() as $section)
                                    <li x-data="{ showSubMenu: false }"
                                        class="relative">
                                        <a href="{{ route($section->route) }}"
                                           @class([
                                               'menu-link group',
                                               'menu-link--active' => $section->isActive(
                                                   $component->attributes->get('current')
                                               ),
                                               'menu-link--inactive !text-gray-700' => !$section->isActive(
                                                   $component->attributes->get('current')
                                               ),
                                           ])>
                                            <span>
                                                {!! $section->renderIcon('w-5 h-5') !!}
                                            </span>

                                            <span class="text-sm font-medium">
                                                {{ $section->name }}
                                            </span>

                                            @if (count($section->getItems()))
                                                <button x-on:click.prevent="showSubMenu = !showSubMenu"
                                                        class="p-1 ml-auto text-gray-600 bg-white border border-gray-200 rounded">
                                                    <span :class="{ '-rotate-180': showSubMenu }"
                                                          class="block transition">
                                                        <x-hub::icon ref="chevron-down"
                                                                     class="w-3 h-3" />
                                                    </span>
                                                </button>
                                            @endif
                                        </a>

                                        @if (count($section->getItems()))
                                            <div x-show="showSubMenu"
                                                 class="bg-white">
                                                <ul class="space-y-1"
                                                    :class="{
                                                        'border-l border-gray-100 ml-[18px] pl-[18px] mt-1': showSubMenu,
                                                    }">
                                                    @foreach ($section->getItems() as $item)
                                                        <li>
                                                            <a href="{{ route($item->route) }}"
                                                               @class([
                                                                   'flex text-sm font-medium',
                                                                   'text-blue-600 hover:text-blue-500' => $item->isActive(
                                                                       $component->attributes->get('current')
                                                                   ),
                                                                   'text-gray-500 hover:text-blue-600' => !$item->isActive(
                                                                       $component->attributes->get('current')
                                                                   ),
                                                               ])>
                                                                {{ $item->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if (Auth::user()->can('settings'))
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <a href="{{ route('hub.settings') }}"
                       @class([
                           'menu-link group',
                           'menu-link--active' => Str::contains(request()->url(), 'settings'),
                           'menu-link--inactive !text-gray-700' => !Str::contains(
                               request()->url(),
                               'settings'
                           ),
                       ])>
                        <span>
                            {!! Lunar\Hub\LunarHub::icon('cog', 'w-5 h-5') !!}
                        </span>

                        <span class="text-sm font-medium">
                            {{ __('adminhub::global.settings') }}
                        </span>
                    </a>
                </div>
            @endif
        </x-hub::menu>
    </div>
</x-hub::slideover-simple>
