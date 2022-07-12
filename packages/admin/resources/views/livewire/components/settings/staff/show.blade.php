<div class="space-y-4">
    <header>
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.staff.show.title') }}
        </h1>
    </header>

    <form wire:submit.prevent="update"
          method="POST"
          class="space-y-4">
        @include('adminhub::partials.forms.staff.fields')
    </form>
</div>
