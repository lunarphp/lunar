<div class="space-y-4">
    <header>
        <h1 class="text-xl font-semibold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.customer-groups.create.title') }}
        </h1>
    </header>

    <form action="#"
          method="POST"
          wire:submit.prevent="create">
        @include('adminhub::partials.forms.customer-group')
    </form>
</div>
