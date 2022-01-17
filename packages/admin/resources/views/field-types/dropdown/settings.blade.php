<div
  class="space-y-4"
  x-data="{
    lookups: @entangle('attribute.configuration.lookups'),
    addRow() {
      this.lookups.push({
        label: '',
        value: '',
      })
      this.lookups = JSON.parse(
        JSON.stringify(this.lookups)
      )
    },
    removeRow(index) {
        this.lookups = this.lookups.filter((item, itemIndex) => {
            return index !== itemIndex
        })
    }
  }"
  x-init="
    lookups = Array.isArray(lookups) ? lookups : []

    getKey = () => {
      return btoa(Math.random().toString()).substr(10, 5)
    }

    update = () => {
      lookups = JSON.parse(
        JSON.stringify(lookups)
      )
    }

    new Sortable($refs.list, {
      animation: 150,
      handle: '.handle',
      onSort: ({ newIndex, oldIndex }) => {

        list = JSON.parse(
          JSON.stringify(lookups)
        )

        const moved = list[oldIndex]
        const node = list[newIndex]

        list.splice(oldIndex, 1)
        list.splice(newIndex, 0, moved)

        lookups = list
      }
    });
  "
>
  <div>
    <x-hub::table row-ref="list">
      <x-slot name="head">
          <x-hub::table.heading></x-hub::table.heading>
          <x-hub::table.heading>
            {{ __('adminhub::fieldtypes.dropdown.label_heading') }}
          </x-hub::table.heading>
          <x-hub::table.heading>
            {{ __('adminhub::fieldtypes.dropdown.value_heading') }}
          </x-hub::table.heading>
          <x-hub::table.heading></x-hub::table.heading>
      </x-slot>
      <x-slot name="body">
        <template x-for="(lookup, index) in lookups" :key="getKey()">
          <x-hub::table.row>
            <x-hub::table.cell>
              <x-hub::icon ref="selector" style="solid" class="mr-2 text-gray-400 hover:text-gray-700 handle cursor-grab" />
            </x-hub::table.cell>
            <x-hub::table.cell>
              <x-hub::input.text type="text" @change="update()" x-model.lazy="lookup.label" />
            </x-hub::table.cell>
            <x-hub::table.cell>
              <x-hub::input.text type="text" @change="update()" x-model.lazy="lookup.value" placeholder="{{ __('adminhub::fieldtypes.dropdown.value_placeholder') }}" />
            </x-hub::table.cell>
            <x-hub::table.cell>
            <button
              type="button"
              class="text-gray-500 hover:text-red-500"
              x-on:click.debounce.100ms="removeRow(index)"
              wire:loading.attr="disabled"
            >
              <x-hub::icon ref="x" style="solid" class="w-3" />
            </button>
            </x-hub::table.cell>
          </x-hub::table.row>
        </template>
      </x-slot>
    </x-hub::table>
  </div>

  <button
    type="button"
    class="block w-full py-2 mt-2 text-xs font-bold text-gray-400 uppercase bg-gray-100 rounded hover:bg-gray-200"
    x-on:click="addRow"
  >
    {{ __('adminhub::fieldtypes.dropdown.add_row_btn') }}
  </button>

  @if($errors->has('attribute.configuration.lookups.*.label'))
    <x-hub::alert level="danger">
      {{ __('adminhub::fieldtypes.dropdown.missing_labels') }}
    </x-hub::alert>
  @endif
</div>