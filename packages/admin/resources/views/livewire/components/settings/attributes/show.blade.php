<div>
  <div class="text-right">
    <x-hub::button wire:click.prevent="$set('showGroupCreate', true)">Create Attribute Group</x-hub::button>
  </div>
  <div
    wire:sort
    sort.options='{group: "groups", method: "sortGroups"}'
    class="mt-8 space-y-2"
  >
    @foreach($sortedAttributeGroups as $group)
      <div
        wire:key="group_{{ $group->id }}"
        x-data="{ expanded: false }"
        sort.item="groups"
        sort.id="{{ $group->id }}"
      >
        <div
          class="flex items-center"
        >
          <div wire:loading wire:target="sort">
              <x-hub::icon ref="refresh" style="solid" class="w-5 mr-2 text-gray-300 rotate-180 animate-spin" />
          </div>

          <div wire:loading.remove wire:target="sort">
            <div sort.handle class="cursor-grab">
              <x-hub::icon ref="selector" style="solid" class="mr-2 text-gray-400 hover:text-gray-700" />
            </div>
          </div>

          <div class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300">
              <div class="flex items-center justify-between expand">
                {{ $group->translate('name') }}
              </div>
              <button @click="expanded = !expanded">
                <div class="transition-transform" :class="{
                  '-rotate-90 ': expanded
                }">
                  <x-hub::icon ref="chevron-left" style="solid"  />
                </div>
              </button>
          </div>
        </div>
        @if($group->attributes->count())
          <div
            class="py-4 pl-2 pr-4 mt-2 space-y-2 bg-black border-l rounded bg-opacity-5 ml-7"
            wire:sort
            sort.options='{group: "attributes", method: "sortAttributes"}'
            x-show="expanded"
          >
            @foreach($group->attributes as $attribute)
              <div
                class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300"
                wire:key="attribute_{{ $attribute->id }}"
                sort.item="attributes"
                sort.parent="{{ $group->id }}"
                sort.id="{{ $attribute->id }}"
              >
                <div sort.handle class="cursor-grab">
                  <x-hub::icon ref="selector" style="solid" class="mr-2 text-gray-400 hover:text-gray-700" />
                </div>
                  <span class="grow">{{ $attribute->translate('name') }}</span>
                  <x-hub::icon ref="cog" style="solid" class="w-4" />

                  <x-hub::dropdown minimal>
                    <x-slot name="options">
                      <x-hub::dropdown.link href="" class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50">
                        {{ __('adminhub::catalogue.collections.groups.node.edit') }}
                        <x-hub::icon ref="pencil" style="solid" class="w-4" />
                      </x-hub::dropdown.link>
                    </x-slot>
                  </x-hub::dropdown>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    @endforeach
  </div>

  <x-hub::slideover wire:model="showGroupCreate">
    @livewire('hub.components.settings.attributes.attribute-group-create', [
      'typeHandle' => $type,
      'attributableType' => $this->typeClass,
    ])
  </x-hub::sliderover>
</div>