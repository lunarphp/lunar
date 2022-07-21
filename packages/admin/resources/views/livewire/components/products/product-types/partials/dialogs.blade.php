<x-hub::modal.dialog wire:model="deleteDialogVisible">
    <x-slot name="title">
        {{ __('adminhub::catalogue.product-types.show.delete.confirm_text') }}
    </x-slot>

    <x-slot name="content">
        @if ($this->canDelete)
            {{ __('adminhub::catalogue.product-types.show.delete.message') }}
        @else
            {{ __('adminhub::catalogue.product-types.show.delete.disabled_message') }}
        @endif
    </x-slot>

    <x-slot name="footer">
        <div class="flex items-center justify-end space-x-4">
            <x-hub::button theme="gray" type="button" wire:click="$set('deleteDialogVisible', false)">
                {{ __('adminhub::global.cancel') }}
            </x-hub::button>

            <x-hub::button wire:click="delete"
                           :disabled="!$this->canDelete">
                {{ __('adminhub::catalogue.product-types.show.delete.confirm_text') }}
            </x-hub::button>
        </div>
    </x-slot>
</x-hub::modal.dialog>

<x-hub::modal.dialog wire:model="removeGroupId">
    <x-slot name="title">{{ __('Detach group') }}</x-slot>
    <x-slot name="content">
        @if ($this->canDelete)
            {{ __('Are you sure you want to detach this group?') }}
        @else
            {{ __('adminhub::catalogue.product-types.show.delete.disabled_message') }}
        @endif
    </x-slot>
    <x-slot name="footer">
        <div class="flex items-center justify-end space-x-4">
            <x-hub::button theme="gray" type="button" wire:click="$set('removeGroupId', false)">
                {{ __('adminhub::global.cancel') }}
            </x-hub::button>

            <x-hub::button wire:click="detachGroup" :disabled="!$this->canDelete">
                {{ __('Confirm detach') }}
            </x-hub::button>
        </div>
    </x-slot>
</x-hub::modal.dialog>

<x-hub::modal.dialog wire:model="removeGroupValueId">
    <x-slot name="title">{{ __('Detach group value') }}</x-slot>
    <x-slot name="content">
        @if ($this->canDelete)
            {{ __('Are you sure you want to detach this group value?') }}
        @else
            {{ __('adminhub::catalogue.product-types.show.delete.disabled_message') }}
        @endif
    </x-slot>
    <x-slot name="footer">
        <div class="flex items-center justify-end space-x-4">
            <x-hub::button theme="gray" type="button" wire:click="$set('removeGroupValueId', false)">
                {{ __('adminhub::global.cancel') }}
            </x-hub::button>

            <x-hub::button wire:click="detachGroupValue" :disabled="!$this->canDelete">
                {{ __('Confirm detach') }}
            </x-hub::button>
        </div>
    </x-slot>
</x-hub::modal.dialog>

<x-hub::modal.dialog form="assignGroup" wire:model="showGroupAssign">
    <x-slot name="title">
        {{ __('Attach Product Option') }}
    </x-slot>

    <x-slot name="content">
        <x-hub::input.group :label="__('Options')" for="option" required :error="$errors->first('options')">
            <x-hub::input.select wire:model="selectedGroupId">
                @foreach ($this->availableGroupOptions as $option)
                    <option value="{{ $option['id'] }}" @disabled($option['disabled'])>{{ $option['name'] }}</option>
                @endforeach
            </x-hub::input.select>
        </x-hub::input.group>
    </x-slot>

    <x-slot name="footer">
        <x-hub::button type="button" wire:click.prevent="$set('showGroupAssign', false)" theme="gray">
            {{ __('adminhub::global.cancel') }}
        </x-hub::button>
        <x-hub::button type="submit" theme="green">
            {{ __('Attach') }}
        </x-hub::button>
    </x-slot>
</x-hub::modal.dialog>

<x-hub::modal.dialog form="attachToGroup" wire:model="attachValueToGroupId">
    <x-slot name="title">
        {{ __('Attach to group') }}
    </x-slot>

    <x-slot name="content">
        <x-hub::input.group :label="__('Options')" for="option" required :error="$errors->first('options')">
            <x-hub::input.select wire:model="selectedGroupValueId">
                @foreach ($this->availableGroupValueOptions as $option)
                    <option value="{{ $option['id'] }}" @disabled($option['disabled'])>{{ $option['name'] }}</option>
                @endforeach
            </x-hub::input.select>
        </x-hub::input.group>
    </x-slot>

    <x-slot name="footer">
        <x-hub::button type="button" wire:click.prevent="$set('showGroupValueAssign', false)" theme="gray">
            {{ __('adminhub::global.cancel') }}
        </x-hub::button>
        <x-hub::button type="submit" theme="green">
            {{ __('Attach') }}
        </x-hub::button>
    </x-slot>
</x-hub::modal.dialog>
