<div class="mx-12 lg:mx-24">
    <div class="flex items-center space-x-4">
      {{--
        Product title.
       --}}
      <a href="{{ route('hub.products.index') }}" class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white" title="{{ __('adminhub::catalogue.products.show.back_link_title') }}">
        <x-hub::icon ref="chevron-left" style="solid" class="w-8 h-8" />
      </a>
      <h1 class="text-xl font-bold md:text-xl">
        @if($discount->id)
          {{ $discount->translateAttribute('name') }}
        @else
        {{ __('adminhub::global.new_product') }}
        @endif
      </h1>
    </div>

    <div class="py-12 pb-24 lg:grid lg:grid-cols-12 lg:gap-x-12">
      <div class="sm:px-6 lg:px-0 lg:col-span-9">
        <div class="space-y-6">
            {{--
            Attributes
           --}}
          <div id="attributes">
            @include('adminhub::partials.attributes')
          </div>
          <div
            class="flex-col space-y-4 bg-white rounded px-4 py-5 sm:p-6"
          >
            <header>
              <h3 class="text-lg font-medium leading-6 text-gray-900">
                Conditions
              </h3>
            </header>
            @foreach ($this->conditions as $condition)
                <div class="divide-y">
                    @livewire($condition->driver()->editComponent(), [
                        'condition' => $condition,
                    ], key($condition->id.'-condition'))
                </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
</div>
