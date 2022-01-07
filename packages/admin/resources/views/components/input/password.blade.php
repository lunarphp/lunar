<div class="flex items-center" x-data="{
  type: 'password'
}">
<input
  {{ $attributes->except('type')->merge([
    'class' => 'form-input block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed',
  ])->class([
    'border-red-400' => !!$error,
  ]) }}
  maxlength="255"
  :type="type"
>
  <button class="ml-2 text-red-400" x-on:click="type = 'password'" x-show="type == 'text'">
    <x-hub::icon  ref="eye-off" style="solid" class="w-6" />
  </button>
  <button class="ml-2 text-gray-300 hover:text-gray-400" x-on:click="type = 'text'" x-show="type == 'password'">
    <x-hub::icon  ref="eye" style="solid" class="w-6" />
  </button>
</div>
