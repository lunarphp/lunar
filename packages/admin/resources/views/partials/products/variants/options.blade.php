<div class="overflow-hidden shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        Basic Information
      </h3>
    </header>
    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-4">
        <x-hub::input.group label="Size" for="sku">
          <x-hub::input.text value="Small" />
        </x-hub::input.group>

        <x-hub::input.group label="Colour" for="sku">
          <x-hub::input.text value="Red" />
        </x-hub::input.group>

        <x-hub::input.group label="Material" for="sku">
          <x-hub::input.text value="plastic" />
        </x-hub::input.group>
      </div>
      <div class="space-y-4">
        <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
          <div class="space-y-1 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
              <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <div class="flex text-sm text-gray-600">
              <label for="file-upload" class="relative font-medium text-indigo-600 bg-white rounded-md cursor-pointer hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                <span>Upload a file</span>
                <input id="file-upload" name="file-upload" type="file" class="sr-only">
              </label>
              <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs text-gray-500">
              PNG, JPG, GIF up to 10MB
            </p>
          </div>
        </div>
        <div>
          <x-hub::button theme="gray">Choose existing</x-hub::button>
        </div>
      </div>

    </div>
  </div>
</div>
