<div class="space-y-4">
    <div class="shadow sm:rounded-md">
        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <x-hub::input.group :label="__('adminhub::inputs.name')"
                                for="name"
                                :error="$errors->first('brand.name.' . $this->defaultLanguage->code)">
                <x-hub::input.text wire:model.defer="brand.name"
                                   :error="$errors->first('brand.name')"
                                   :placeholder="__('adminhub::components.attribute-group-edit.name.placeholder')" />
            </x-hub::input.group>
        </div>
    </div>
</div>
