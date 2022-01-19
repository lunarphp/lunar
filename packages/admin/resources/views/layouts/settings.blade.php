@extends('adminhub::layouts.app')

@section('main')
  <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
    <div class="grid items-center grid-cols-2 mb-6 md:mb-8">
      <div>
          <strong class="text-xl font-bold md:text-2xl">{{ $title }}</strong>
      </div>
      <div class="relative space-y-1 text-right md:hidden" x-data="{ menuVisible: false }">
        <button @click.prevent="menuVisible = true" :class="menuVisible ? 'bg-gray-200 ' : ''" class="inline-flex items-center flex-shrink-0 px-4 py-2 text-xs font-bold text-gray-600 uppercase border rounded ">
          {{ __('adminhub::settings.layout.menu_btn') }}
          <x-hub::icon ref="chevron-down" style="solid" />
        </button>
        <nav class="absolute right-0 z-50 py-2 bg-white rounded shadow-lg md:hidden" x-show="menuVisible" @click.outside="menuVisible = false">
          <x-hub::menu handle="settings" current="{{ request()->route()->getName() }}">
            @foreach($component->items as $item)
              <a href="{{ route($item->route) }}" class="block px-6 py-2 text-sm">
                <span>{{ $item->name }}</span>
              </a>
            @endforeach
          </x-hub::menu>
        </nav>
      </div>
    </div>
    <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
      <aside class="hidden px-2 py-6 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3 md:block">
        <nav class="space-y-1">
          <x-hub::menu handle="settings" current="{{ request()->route()->getName() }}">
            @foreach($component->items as $item)
            <a
              href="{{ route($item->route) }}"
              class="flex items-center px-3 py-2 text-sm font-medium @if(!$item->isActive($component->attributes->get('current'))) text-gray-900 hover:text-gray-900 hover:bg-gray-50 @else bg-gray-50 text-indigo-700 hover:text-indigo-700 hover:bg-white @endif rounded-md  group"
              aria-current="page"
            >
              <span class="@if(!$item->isActive($component->attributes->get('current'))) text-gray-400 group-hover:text-gray-500 @else text-indigo-500 group-hover:text-indigo-500 @endif">
                  {!! $item->renderIcon('flex-shrink-0 w-6 h-6 mr-3 -ml-1') !!}
              </span>
              <span class="truncate">
                {{ $item->name }}
              </span>
            </a>
            @endforeach
          </x-hub::menu>
        </nav>
      </aside>

      <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
        {{ $slot }}
      </div>
    </div>
  </div>
@stop
