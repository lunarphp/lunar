<div class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-200">
    {{ __('adminhub::components.activity-log.orders.status_change') }}

    <div class="flex items-center gap-1 ml-2">
        <strong>
            <x-hub::orders.status :status="$log->getExtraProperty('previous')" />
        </strong>

        <x-hub::icon ref="chevron-right"
                     style="solid"
                     class="w-4" />

        <strong>
            <x-hub::orders.status :status="$log->getExtraProperty('new')" />
        </strong>
    </div>
</div>
