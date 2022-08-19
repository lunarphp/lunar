<div class="space-y-4">
    <header>
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ $discount->name }}
        </h1>
    </header>

    <form action="#"
          method="POST"
          wire:submit.prevent="save">
        @include('adminhub::partials.forms.discount')
    </form>
</div>
