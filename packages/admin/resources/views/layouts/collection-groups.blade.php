@extends('adminhub::layouts.app')

@section('menu')
    <x-hub::side-menu-layout>
        @livewire('hub.components.collections.sidemenu', [
            'currentGroup' => $group ?? null,
        ])
    </x-hub::side-menu-layout>
@stop

@section('main')
    <div class="space-y-6">
        <div x-data="{ showCollectionGroups: false }">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold md:text-xl">
                    {{ __('adminhub::catalogue.collections.index.title') }}
                </h1>

                <div class="block lg:hidden">
                    <x-hub::button theme="gray"
                                   x-on:click="showCollectionGroups = !showCollectionGroups">
                        {{ __('View Collection Groups') }}
                    </x-hub::button>
                </div>
            </div>

            <div class="relative z-40 lg:hidden"
                 role="dialog"
                 aria-modal="true">
                <div class="fixed inset-0 bg-gray-600/75"
                     x-show="showCollectionGroups"
                     x-cloak
                     aria-hidden="true"></div>

                <div class="fixed inset-0 z-40 flex"
                     x-show="showCollectionGroups"
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full">
                    <div class="w-full max-w-xs p-4 overflow-y-auto bg-white focus:outline-none"
                         x-on:click.away="showCollectionGroups = false">
                        @livewire('hub.components.collections.sidemenu', [
                            'currentGroup' => $group ?? null,
                        ])
                    </div>
                </div>
            </div>
        </div>

        {{ $slot }}
    </div>
@stop
