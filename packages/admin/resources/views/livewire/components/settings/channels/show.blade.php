<div class="space-y-4">
    <header>
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.channels.show.title') }}
        </h1>
    </header>

    <div class="space-y-4">
        <div class="overflow-hidden shadow sm:rounded-md">
            @livewire('hub.components.forms.channel-form', ['model' => $channel])
        </div>
    </div>
</div>
