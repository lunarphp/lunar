
  <div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
      <header class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            {{ __('adminhub::catalogue.collections.index.title') }}
          </h3>
          <p class="text-sm text-gray-500">{{ __('adminhub::catalogue.collections.associate_to_other_products') }}</p>
        </div>
      </header>

      <div class="space-y-4">
          <div>
            <header class="p-4 text-sm border rounded-t">
              <strong>{{ __('adminhub::catalogue.collections.static_collections') }}</strong>
            </header>
            <div class="relative">
              <x-hub::icon ref="search" class="absolute w-5 mt-2 ml-3 text-gray-500" />
              <input class="block w-full px-4 py-3 pl-10 text-sm border border-t-0 bg-gray-50" placeholder="Search for collections" />
            </div>
            <div class="border border-t-0 rounded-b">
              <div class="grid items-center grid-cols-12 p-4 text-sm">
                <div class="col-span-11">
                  <a class="block text-sm text-indigo-500 truncate hover:underline" href="#">{{ __('adminhub::catalogue.collections.summer_seasonal') }}</a>
                </div>
                <div class="col-span-1 text-right">
                  <button>
                    <x-hub::icon ref="x" style="solid" class="w-4 h-4 mt-2" />
                  </button>
                </div>
              </div>
            </div>
          </div>
      </div>
    </div>
</div>
