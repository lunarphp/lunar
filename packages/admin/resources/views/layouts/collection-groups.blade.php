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
        <div x-data="{ showGroupSlideover: false }">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold md:text-xl">
                    {{ __('adminhub::catalogue.collections.index.title') }}
                </h1>

                <div class="block lg:hidden">
                    <x-hub::button type="button"
                                   theme="gray"
                                   x-on:click="showGroupSlideover = !showGroupSlideover">
                        {{ __('View Collection Groups') }}
                    </x-hub::button>
                </div>
            </div>

            <x-hub::slideover-simple target="showGroupSlideover">
                @livewire('hub.components.collections.sidemenu', [
                    'currentGroup' => $group ?? null,
                ])
            </x-hub::slideover-simple>
        </div>

        {{ $slot }}
    </div>
@stop
