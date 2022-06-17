<div class="flex-col px-12 space-y-4">
  <div class="flex items-center justify-between">
    <strong class="text-xl font-bold md:text-2xl">
      {{ __('adminhub::components.discounts.index.title') }}
    </strong>

    <div class="text-right">
      <x-hub::button tag="a" href="{{ route('hub.products.create') }}">{{ __('adminhub::components.discounts.index.create_discount') }}</x-hub::button>
    </div>
  </div>

  <div class="space-y-4">
    <x-hub::table>
      <x-slot name="head">
        <x-hub::table.heading>
          {{ __('adminhub::global.name') }}
        </x-hub::table.heading>

        <x-hub::table.heading>
          {{ __('adminhub::global.starts_at') }}
        </x-hub::table.heading>

        <x-hub::table.heading>
          {{ __('adminhub::global.ends_at') }}
        </x-hub::table.heading>

        <x-hub::table.heading>
          {{ __('adminhub::global.priority') }}
        </x-hub::table.heading>

        <x-hub::table.heading>
          {{ __('adminhub::global.stop') }}
        </x-hub::table.heading>

        <x-hub::table.heading></x-hub::table.heading>

      </x-slot>
      <x-slot name="body">
        @foreach($this->discounts as $discount)
          <x-hub::table.row>
            <x-hub::table.cell>
              {{ $discount->name }}
            </x-hub::table.cell>

            <x-hub::table.cell>
                {{ $discount->starts_at->format('jS M Y @ h:ma') }}
            </x-hub::table.cell>

            <x-hub::table.cell>
              {{ $discount->ends_at?->format('jS M Y @ h:ma') }}
            </x-hub::table.cell>

            <x-hub::table.cell>
              {{ $discount->priority }}
            </x-hub::table.cell>

            <x-hub::table.cell>
              {{ $discount->stop }}
            </x-hub::table.cell>

            <x-hub::table.cell>
              <a href="{{ route('hub.discounts.show', $discount->id) }}" class="text-indigo-500 hover:underline">
                {{ __('adminhub::global.view') }}
              </a>
            </x-hub::table.cell>
          </x-hub::table.row>
        @endforeach
      </x-slot>
    </x-hub::table>
    <div>
      {{ $this->discounts->links() }}
    </div>
  </div>
</div>
