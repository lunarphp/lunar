<div class="space-y-4">
    <div class="overflow-hidden shadow sm:rounded-md">
        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <div class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
                <x-hub::input.group :label="__('adminhub::inputs.name')"
                                    for="name"
                                    :error="$errors->first('language.name')">
                    <x-hub::input.text wire:model="language.name"
                                       name="name"
                                       id="name"
                                       :error="$errors->first('language.name')" />
                </x-hub::input.group>
                <x-hub::input.group :label="__('adminhub::inputs.code')"
                                    for="code"
                                    :error="$errors->first('language.code')">
                    <x-hub::input.text wire:model="language.code"
                                       name="code"
                                       id="code"
                                       :error="$errors->first('language.code')" />
                </x-hub::input.group>
            </div>
            <x-hub::input.group label="Default"
                                for="handle"
                                :instructions="__('adminhub::settings.languages.form.default_instructions')">
                <x-hub::input.toggle wire:click="toggleDefault"
                                     :on="$language->default"
                                     name="handle"
                                     id="handle"
                                     :disabled="$language->id && $language->getOriginal('default')" />
            </x-hub::input.group>
        </div>
        <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
            <button type="submit"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __(
                    $language->id ? 'adminhub::settings.languages.form.update_btn' : 'adminhub::settings.languages.form.create_btn',
                ) }}
            </button>
        </div>
    </div>
    @if ($language->id && !$language->getOriginal('default'))
        <div class="bg-white border border-red-300 rounded shadow">
            <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
                {{ __('adminhub::inputs.danger_zone.title') }}
            </header>
            <div class="p-6 text-sm">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6">
                        <strong>{{ __('adminhub::inputs.danger_zone.label', ['model' => 'language']) }}</strong>
                        <p class="text-xs text-gray-600">
                            {{ __('adminhub::inputs.danger_zone.instructions', ['attribute' => 'name', 'model' => 'language']) }}
                        </p>
                    </div>
                    <div class="col-span-9 lg:col-span-4">
                        <x-hub::input.text wire:model="deleteConfirm" />
                    </div>
                    <div class="col-span-3 text-right lg:col-span-2">
                        <x-hub::button :disabled="!$this->canDelete"
                                       wire:click="delete"
                                       type="button"
                                       theme="danger">{{ __('adminhub::global.delete') }}</x-hub::button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
