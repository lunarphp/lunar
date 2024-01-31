<div class="space-y-4">
    <div class="overflow-hidden shadow sm:rounded-md">
        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <x-hub::input.group
                    :label="__('adminhub::inputs.name')"
                    for="name"
                    :error="$errors->first('customerGroup.name')"
            >
                <x-hub::input.text
                        wire:model="customerGroup.name"
                        :error="$errors->first('customerGroup.name')"
                        :placeholder="__('adminhub::components.attribute-edit.name.placeholder')"
                        wire:change="formatHandle"
                />
            </x-hub::input.group>

            <x-hub::input.group :label="__('adminhub::inputs.handle')"
                                for="handle"
                                :error="$errors->first('customerGroup.handle')">
                <x-hub::input.text wire:model.debounce.350ms="customerGroup.handle"
                                   name="handle"
                                   id="handle"
                                   :error="$errors->first('customerGroup.handle')" />
            </x-hub::input.group>

            <x-hub::input.group :label="__('adminhub::globals.default')"
                                for="handle"
                                :instructions="__('adminhub::settings.customer-groups.form.default_instructions')">
                <x-hub::input.toggle wire:model="customerGroup.default"
                                     :on="$customerGroup->default"
                                     name="handle"
                                     id="handle"
                                     :disabled="$customerGroup->id && $customerGroup->getOriginal('default')" />
            </x-hub::input.group>
        </div>

        <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
            <button type="submit"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-sky-600 border border-transparent rounded-md shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                {{ __('adminhub::global.save') }}
            </button>
        </div>
    </div>

   @if ($customerGroup->id && !$customerGroup->getOriginal('default'))
       <div class="bg-white border border-red-300 rounded shadow">
            <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
               {{ __('adminhub::inputs.danger_zone.title') }}
            </header>

           <div class="p-6 text-sm">
               <div class="grid grid-cols-12 gap-4">
                   <div class="col-span-12 md:col-span-6">
                       <strong>{{ __('adminhub::partials.forms.customer-group.delete_customer_group') }}</strong>

                       <p class="text-xs text-gray-600">
                           {{ __('adminhub::partials.forms.customer-group.customer_group_name_delete') }}</p>
                   </div>

                   <div class="col-span-9 lg:col-span-4">
                       <x-hub::input.text wire:model="deleteConfirm" />
                   </div>

                   <div class="col-span-3 text-right lg:col-span-2">
                       <x-hub::button :disabled="!$this->canDelete"
                                    theme="danger"
                                    wire:click="delete"
                                    type="button">{{ __('adminhub::global.delete') }}</x-hub::button>
                   </div>
               </div>
           </div>
       </div>
   @endif
</div>
