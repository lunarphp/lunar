@extends('adminhub::layouts.app')

@section('menu')
    <div>
        <aside class="hidden h-full lg:block lg:flex-shrink-0 lg:order-first">
            <div class="relative flex flex-col h-full overflow-y-auto bg-white border-r border-gray-100 w-72">
                <div class="px-4 py-16">
                    @livewire('hub.components.collections.sidemenu', [
                        'currentGroup' => $group ?? null,
                    ])
                </div>
            </div>
        </aside>
    </div>
@stop

@section('main')
    <div class="space-y-6">
        <div x-data="{ showCollectionGroups: false }">
            <div class="flex items-center justify-between">
                <strong class="text-xl font-bold md:text-2xl">
                    {{ __('adminhub::catalogue.collections.index.title') }}
                </strong>

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
