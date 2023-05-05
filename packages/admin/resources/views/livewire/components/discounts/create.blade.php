<div class="space-y-4">
    <header>
        <h1 class="text-xl font-semibold text-gray-900 md:text-2xl dark:text-white">
            @if($discount->id)
                {{ $discount->name }}
            @else
                Create Discount
            @endif
        </h1>
    </header>

    <form action="#"
          method="POST"
          wire:submit.prevent="save">
        @include('adminhub::partials.forms.discount')
    </form>
</div>



    {{-- <div class="py-12 pb-24 mt-6">
      <div class="sm:px-6 lg:px-0 lg:col-span-9">
        <div class="space-y-6">
            <div
              class="flex-col space-y-4 bg-white rounded px-4 py-5 sm:p-6"
            >

                 <x-hub::input.group for="type" label="Limit discount">
                    <x-hub::input.select wire:model="discount.restriction">
                        <option value>All products</option>
                        <option value="products">Specific products</option>
                        <option value="collection">Products in collections</option>
                    </x-hub::input.select>
                </x-hub::input.group>
            </div>

        </div>
      </div>
    </div> --}}
