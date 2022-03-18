
  <div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
      <header class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            {{ __('adminhub::menu.product.associations.title') }}
          </h3>
          <p class="text-sm text-gray-500">{{ __('adminhub::menu.product.associations.explain') }}</p>
        </div>
      </header>

      <div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <div class="rounded-b shadow-sm">
              <header class="p-4 text-sm border rounded-t">
                <strong>{{ __('adminhub::partials.products.associations.cross-sell') }}</strong>

              </header>
              <div class="relative">
                <x-hub::icon ref="search" class="absolute w-5 mt-2 ml-3 text-gray-500" />
                <input class="block w-full px-4 py-3 pl-10 text-sm border border-t-0 bg-gray-50" placeholder="Search for products" />
              </div>
              <div class="border border-t-0 rounded-b">
                <div class="grid items-center grid-cols-12 p-4 text-sm">
                  <div class="col-span-2">
                    <img src="https://images.unsplash.com/photo-1539185441755-769473a23570?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=40&q=80">
                  </div>
                  <div class="col-span-9">
                    <a class="block text-xs text-indigo-500 truncate hover:underline" href="#">{{ __('adminhub::partials.products.associations.alfra_piccolo') }}</a>
                    <span class="text-gray-600">{{ __('adminhub::partials.products.associations.20054123') }}</span>
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

          <div>
            <div class="rounded-b shadow-sm">
              <header class="p-4 text-sm border rounded-t">
                <strong>{{ __('adminhub::partials.products.associations.up-sell') }}</strong>
              </header>
              <div class="relative">
                <x-hub::icon ref="search" class="absolute w-5 mt-2 ml-3 text-gray-500" />
                <input class="block w-full px-4 py-3 pl-10 text-sm border border-t-0 bg-gray-50" placeholder="Search for products" />
              </div>
              <div class="text-sm border border-t-0 rounded-b">
                <p class="p-4 text-sm text-gray-600">{{ __('adminhub::partials.products.associations.up-sell_selecting_products') }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
</div>
