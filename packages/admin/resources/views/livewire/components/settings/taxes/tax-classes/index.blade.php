
<div class="flex-col space-y-4">
  @include('adminhub::partials.navigation.taxes')

  <x-hub::modal.dialog form="save" wire:model="taxClass">
      <x-slot name="title">
        {{ __('adminhub::settings.taxes.tax-classes.index.update.title') }}
      </x-slot>

      <x-slot name="content">
        <x-hub::input.group label="Name" for="name">
          <x-hub::input.text wire:model="taxClass.name" />
        </x-hub::input.group>

        @if($this->taxClass)
          <x-hub::input.group label="Default" for="default">
            <x-hub::input.toggle wire:model="taxClass.default"/>
          </x-hub::input.group>
        @endif
      </x-slot>

      <x-slot name="footer">
        <x-hub::button type="button" wire:click.prevent="$set('taxClass', null)" theme="gray">
          {{ __('adminhub::global.cancel') }}
        </x-hub::button>

        <x-hub::button type="submit">
          {{ __('adminhub::global.save') }}
        </x-hub::button>
      </x-slot>
  </x-hub::modal.dialog>

  <x-hub::table>
    <x-slot name="head">
      <x-hub::table.heading>Name</x-hub::table.heading>
      <x-hub::table.heading>Default</x-hub::table.heading>
      <x-hub::table.heading></x-hub::table.heading>
    </x-slot>
    <x-slot name="body">
      @foreach($taxClasses as $taxClass)
        <x-hub::table.row>
          <x-hub::table.cell>
            {{ $taxClass->name }}
          </x-hub::table.cell>

          <x-hub::table.cell>
            <x-hub::icon :ref="$taxClass->default ? 'check' : 'x'" :class="$taxClass->default ? 'text-green-500' : 'text-red-500'" style="solid" />
          </x-hub::table.cell>

          <x-hub::table.cell>
            <a href="#" wire:click.prevent="editTaxClass({{ $taxClass->id }})" class="text-indigo-500 hover:underline">
              {{ __('adminhub::settings.taxes.tax-zones.index.table_row_action_text') }}
            </a>
          </x-hub::table.cell>
        </x-hub::table.row>
      @endforeach
    </x-slot>
  </x-hub::table>
  <div>
    {{ $taxClasses->links() }}
  </div>
</div>
