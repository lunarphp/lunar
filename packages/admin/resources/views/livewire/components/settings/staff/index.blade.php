<div class="space-y-4">
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.staff.index.title') }}
        </h1>

        <div class="mt-4 sm:mt-0">
            <x-hub::button tag="a"
                           href="{{ route('hub.staff.create') }}">
                {{ __('adminhub::settings.staff.index.create_btn') }}
            </x-hub::button>
        </div>
    </header>

    @livewire('hub.components.tables.staff-table')


    {{-- <x-hub::table>
        <x-slot name="head">
            <x-hub::table.heading></x-hub::table.heading>
            <x-hub::table.heading>{{ __('adminhub::global.active') }}</x-hub::table.heading>
            <x-hub::table.heading>{{ __('adminhub::global.firstname') }}</x-hub::table.heading>
            <x-hub::table.heading>{{ __('adminhub::global.lastname') }}</x-hub::table.heading>
            <x-hub::table.heading>{{ __('adminhub::global.email') }}</x-hub::table.heading>
            <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
            @forelse($staff as $staffMember)
                <x-hub::table.row wire:loading.class.delay="opacity-50">
                    <x-hub::table.cell>
                        <x-hub::gravatar email="{{ $staffMember->email }}"
                                         class="w-6 h-6 rounded-full" />
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        <x-hub::icon :ref="!$staffMember->deleted_at ? 'check' : 'x'"
                                     :class="!$staffMember->deleted_at ? 'text-green-500' : 'text-red-500'"
                                     style="solid" />
                    </x-hub::table.cell>
                    <x-hub::table.cell>{{ $staffMember->firstname }}</x-hub::table.cell>
                    <x-hub::table.cell>{{ $staffMember->lastname }}</x-hub::table.cell>
                    <x-hub::table.cell>
                        {{ $staffMember->email }}
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        <a href="{{ route('hub.staff.show', $staffMember->id) }}"
                           class="text-indigo-500 hover:underline">
                            {{ __('adminhub::settings.staff.index.table_row_action_text') }}
                        </a>
                    </x-hub::table.cell>
                </x-hub::table.row>
            @empty
                <x-hub::table.no-results />
            @endforelse
        </x-slot>
    </x-hub::table>
    <div>
        {{ $staff->links() }}
    </div> --}}
</div>
