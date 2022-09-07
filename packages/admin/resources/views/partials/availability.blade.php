<div>
    <div class="shadow sm:rounded-md">
        <div
             class="px-4 py-5 space-y-4 bg-white border border-white rounded-md dark:bg-gray-800 dark:border-gray-700 sm:p-6">
            <header>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('adminhub::partials.availability.heading', [
                        'type' => $type ?? 'product',
                    ]) }}
                </h3>
            </header>

            <x-hub::alert>
                {{ __('adminhub::partials.availability.schedule_notice', [
                    'type' => $type ?? 'product',
                ]) }}
            </x-hub::alert>

            <div class="space-y-4">
                <div class="space-y-4">
                    <header class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                {{ __('adminhub::partials.availability.channel_heading', [
                                    'type' => $type ?? 'product',
                                ]) }}
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('adminhub::partials.availability.channel_strapline', [
                                    'type' => $type ?? 'product',
                                ]) }}
                            </p>
                        </div>
                    </header>

                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @include('adminhub::partials.availability.channels')
                    </div>
                </div>

                @if ($customerGroups)
                    <div>
                        <header class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">
                                    {{ __('adminhub::partials.availability.customer_groups.title') }}
                                </h3>

                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('adminhub::partials.availability.customer_groups.strapline', [
                                        'type' => $type ?? 'product',
                                    ]) }}
                                </p>
                            </div>
                        </header>

                        <div class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                            @include('adminhub::partials.availability.customer-groups')
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
