<div>
    <x-hub::menu handle="sidebar" current="{{ request()->route()->getName() }}">
      <div class="flex-col space-y-3">
        @foreach($component->items as $item)
          <a
            href="{{ route($item->route) }}"
            class="flex items-center mx-5 px-2 py-2 text-base font-medium rounded-md group @if(!$item->isActive($component->attributes->get('current'))) text-gray-400 hover:text-gray-900 @else text-blue-600 @endif"
          >
            {!! $item->renderIcon() !!}
            <span class="ml-2">{{ $item->name }}</span>
          </a>
        @endforeach
      </div>
    </x-hub::menu>

    <div class="mt-4">
      @if(Auth::user()->can('settings'))
        <a href="{{ route('hub.settings') }}" class="flex items-center mx-5 px-2 py-2 text-base rounded-md group @if(!Str::contains(request()->url(), 'settings')) text-gray-400 hover:text-gray-900 @else text-gray-900 bg-gray-100 @endif">
          {!! GetCandy\Hub\GetCandyHub::icon('cog', 'w-6 h-6 mr-2') !!}
          {{ __('adminhub::global.settings') }}
        </a>
      @endif
    </div>
</div>
