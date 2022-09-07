<div class="space-y-4">
    {{ $this->addresses->links() }}

    <div class="grid gap-4 text-sm md:grid-cols-2">
        @foreach ($this->addresses as $address)
            <div wire:key="address_{{ $address->id }}"
                 class="overflow-hidden leading-relaxed bg-white border border-white rounded shadow dark:bg-gray-800 dark:border-gray-700">
                <div class="flex justify-between px-4 py-3 dark:bg-white/5 bg-black/5">
                    <div>
                        @if ($address->billing_default)
                            <span
                                  class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded dark:bg-blue-700 dark:text-blue-50">
                                Billing Default
                            </span>
                        @endif

                        @if ($address->shipping_default)
                            <span
                                  class="px-3 py-1 text-xs text-green-600 bg-green-100 rounded dark:bg-green-700 dark:text-green-50">
                                Shipping Default
                            </span>
                        @endif
                    </div>

                    <div class="flex gap-4">
                        <x-hub::button theme="gray"
                                       size="xs"
                                       wire:click.prevent="$set('addressIdToEdit', '{{ $address->id }}')">
                            Edit
                        </x-hub::button>

                        <x-hub::button theme="danger"
                                       size="xs"
                                       wire:click.prevent="$set('addressToRemove', '{{ $address->id }}')">
                            {{ __('adminhub::components.customers.show.remove_address_btn') }}
                        </x-hub::button>
                    </div>
                </div>

                <div class="p-4 space-y-2 not-italic text-gray-700 dark:text-gray-300">
                    <strong class="block">
                        {{ $address->first_name }} {{ $address->last_name }}
                    </strong>

                    @if ($address->company_name)
                        <p>{{ $address->company_name }}</p>
                    @endif

                    <address class="not-italic">
                        <span class="block">
                            {{ $address->line_one }}
                        </span>

                        @if ($address->line_two)
                            <span class="block">
                                {{ $address->line_two }}
                            </span>
                        @endif

                        @if ($address->line_three)
                            <span class="block">
                                {{ $address->line_three }}
                            </span>
                        @endif

                        <span class="block">
                            {{ $address->city }}
                        </span>

                        <span class="block">
                            {{ $address->state }}
                        </span>

                        <span class="block">
                            {{ $address->postcode }}
                        </span>

                        <span class="block">
                            {{ $address->country->name }}
                        </span>

                        <span class="block">
                            {{ $address->contact_email }}
                        </span>

                        <span class="block">
                            {{ $address->contact_phone }}
                        </span>
                    </address>
                </div>
            </div>
        @endforeach
    </div>
</div>
