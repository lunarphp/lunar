<div>
    <div class="flex items-center gap-4">
        <a href="{{ route('hub.discounts.index') }}"
           class="text-gray-600 rounded bg-gray-50 hover:bg-sky-500 hover:text-white"
           title="{{ __('adminhub::catalogue.products.show.back_link_title') }}">
            <x-hub::icon ref="chevron-left"
                         style="solid"
                         class="w-8 h-8" />
        </a>

        <h1 class="text-xl font-semibold md:text-xl">
            {{ $discount->name }}
        </h1>
    </div>

    <form action="#"
      method="POST"
      wire:submit.prevent="save">
        @include('adminhub::partials.forms.discount')
    </form>
</div>
