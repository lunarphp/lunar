<div class="space-y-4">
    <header>
        <h1 class="text-xl font-semibold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.channels.show.title') }}
        </h1>
    </header>

    <form action="#"
          method="POST"
          wire:submit.prevent="update">
        @include('adminhub::partials.forms.channel')
    </form>
</div>
