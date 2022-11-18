<div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
        <header>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                Availability
            </h3>
        </header>

        @include('adminhub::partials.availability', [
            'channels' => true,
            'type' => 'discount',
            'customerGroups' => true,
        ])
    </div>
</div>
