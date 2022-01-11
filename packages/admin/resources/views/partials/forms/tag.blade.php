<div class="flex-col space-y-4">
    <div class="overflow-hidden shadow sm:rounded-md">
        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <div class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
                <x-hub::input.group :label="__('adminhub::inputs.value')" for="value" :error="$errors->first('tag.value')">
                    <x-hub::input.text wire:model="tag.value" name="value" id="value" :error="$errors->first('tag.value')" />
                </x-hub::input.group>
            </div>
        </div>

        <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
            <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __(
                  $tag->id ? 'adminhub::settings.tags.form.update_btn' : 'adminhub::settings.tags.form.create_btn'
                ) }}
            </button>
        </div>
    </div>
</div>
