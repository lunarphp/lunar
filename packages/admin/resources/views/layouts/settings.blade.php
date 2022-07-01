@extends('adminhub::layouts.app')

@section('main')
    <div>
        <div class="grid items-center grid-cols-2 mb-6 md:mb-8">
            <div>
                <strong class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
                    {{ $title }}
                </strong>
            </div>

            <div class="relative space-y-1 text-right md:hidden"
                 x-data="{ menuVisible: false }">
                <button x-on:click.prevent="menuVisible = true"
                        :class="menuVisible ? 'bg-gray-200' : ''"
                        class="inline-flex items-center flex-shrink-0 px-4 py-2 text-xs font-bold text-gray-600 uppercase border rounded ">
                    {{ __('adminhub::settings.layout.menu_btn') }}

                    <x-hub::icon ref="chevron-down"
                                 style="solid" />
                </button>

                <nav class="absolute right-0 z-50 py-2 bg-white rounded shadow-lg md:hidden"
                     x-show="menuVisible"
                     @click.outside="menuVisible = false">
                    <x-hub::menu handle="settings"
                                 current="{{ request()->route()->getName() }}">
                        @foreach ($component->items as $item)
                            <a href="{{ route($item->route) }}"
                               class="block px-6 py-2 text-sm">
                                <span>{{ $item->name }}</span>
                            </a>
                        @endforeach
                    </x-hub::menu>
                </nav>
            </div>
        </div>

        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-12">
                {{ $slot }}
            </div>
        </div>
    </div>
@stop
