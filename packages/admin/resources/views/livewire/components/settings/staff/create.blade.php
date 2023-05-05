<div class="space-y-4">
    <header>
        <h1 class="text-xl font-semibold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.staff.create.title') }}
        </h1>
    </header>

    <form wire:submit.prevent="create"
          method="POST"
          class="space-y-4">
        @include('adminhub::partials.forms.staff.fields')
    </form>
</div>
