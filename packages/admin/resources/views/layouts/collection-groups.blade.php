@extends('adminhub::layouts.app')

@section('main')
    <div>
        <div class="grid items-center grid-cols-2 mb-6 md:mb-8">
            <div>
                <strong class="text-xl font-bold md:text-2xl">
                    {{ __('adminhub::catalogue.collections.index.title') }}
                </strong>
            </div>
        </div>

        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
            <aside class="hidden px-2 py-6 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3 md:block">
                @livewire('hub.components.collections.sidemenu', [
                    'currentGroup' => $group ?? null,
                ])
            </aside>

            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                {{ $slot }}
            </div>
        </div>
    </div>
@stop
