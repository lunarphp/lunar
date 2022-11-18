<div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
        <header>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                Basic Information
            </h3>
        </header>

        <div class="space-y-4">
            <x-hub::input.group for="name" :label="__('adminhub::inputs.name')" :error="$errors->first('discount.name')">
                <x-hub::input.text wire:model.lazy="discount.name" id="name" />
            </x-hub::input.group>

            <x-hub::input.group for="handle" :label="__('adminhub::inputs.handle')" :error="$errors->first('discount.handle')" required>
                <x-hub::input.text wire:model.defer="discount.handle" id="handle" />
            </x-hub::input.group>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-hub::input.group for="starts_at" :label="__('adminhub::inputs.starts_at.label')">
                        <x-hub::input.datepicker id="starts_at" wire:model="discount.starts_at" :options="['enableTime' => true ]" />
                    </x-hub::input.group>
                </div>

                <div>
                    <x-hub::input.group for="ends_at" :label="__('adminhub::inputs.ends_at.label')" :error="$errors->first('discount.ends_at')">
                        <x-hub::input.datepicker id="ends_at" wire:model="discount.ends_at" :options="['enableTime' => true ]" />
                    </x-hub::input.group>
                </div>
            </div>
        </div>
    </div>
</div>
