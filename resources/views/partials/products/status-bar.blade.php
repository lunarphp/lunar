<div x-data="{ showStatusPicker: false }">
  <div class="relative">
    @if($product->status == 'published')
      <div class="inline-flex divide-x divide-green-600 rounded-md shadow-sm">
        <div class="relative z-0 inline-flex divide-x divide-green-600 rounded-md shadow-sm">
          <div class="relative inline-flex items-center py-2 pl-3 pr-4 text-white bg-green-500 border border-transparent shadow-sm rounded-l-md">
            <!-- Heroicon name: solid/check -->
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <p class="ml-2.5 text-sm font-medium">
              {{ __('adminhub::partials.products.status-bar.published.label')}}
            </p>
          </div>
          <button @click="showStatusPicker = true" type="button" class="relative inline-flex items-center p-2 text-sm font-medium text-white bg-green-500 rounded-l-none rounded-r-md hover:bg-green-600 focus:outline-none focus:z-10 focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-green-500" aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
            <!-- Heroicon name: solid/chevron-down -->
            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    @endif
    @if($product->status == 'draft')
      <div class="inline-flex divide-x divide-yellow-200 rounded-md shadow-sm">
        <div class="relative z-0 inline-flex divide-x divide-yellow-600 rounded-md shadow-sm">
          <div class="relative inline-flex items-center py-2 pl-3 pr-4 text-yellow-900 bg-yellow-500 border border-transparent shadow-sm rounded-l-md">
            <x-hub::icon ref="eye-off" class="w-5 h-5"  />
            <p class="ml-2.5 text-sm font-medium">
              {{ __('adminhub::partials.products.status-bar.draft.label')}}
            </p>
          </div>
          <button @click="showStatusPicker = true" type="button" class="relative inline-flex items-center p-2 text-sm font-medium text-yellow-900 bg-yellow-500 rounded-l-none rounded-r-md hover:bg-yellow-600 focus:outline-none focus:z-10 focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-yellow-500" aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
            <!-- Heroicon name: solid/chevron-down -->
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    @endif
    <!--
      Select popover, show/hide based on select state.

      Entering: ""
        From: ""
        To: ""
      Leaving: "transition ease-in duration-100"
        From: "opacity-100"
        To: "opacity-0"
    -->
    <ul x-cloak x-show="showStatusPicker" @click.away="showStatusPicker = false" class="absolute right-0 z-10 -mt-24 overflow-hidden origin-bottom-right bg-white divide-y divide-gray-200 rounded-md shadow-lg bottom-12 w-72 ring-1 ring-black ring-opacity-5 focus:outline-none" tabindex="-1" role="listbox" aria-labelledby="listbox-label" aria-activedescendant="listbox-option-0">
      <!--
        Select option, manage highlight styles based on mouseenter/mouseleave and keyboard navigation.

        Highlighted: "text-white bg-indigo-500", Not Highlighted: "text-gray-900"
      -->
      <li class="relative p-4 text-sm text-gray-900 cursor-default select-none @if($product->status != 'published') hover:bg-gray-100 @endif" id="listbox-option-0" role="option">
        <button type="button" class="flex flex-col" wire:click.prevent="$set('product.status', 'published')">
          <div class="flex justify-between cursor-pointer">
            <!-- Selected: "font-semibold", Not Selected: "font-normal" -->
            <p class="@if($product->status == 'published') font-semibold @else font-normal @endif">
              {{ __('adminhub::partials.products.status-bar.published.label')}}
            </p>
            <!--
              Checkmark, only display for selected option.

              Highlighted: "text-white", Not Highlighted: "text-indigo-500"
            -->
            @if($product->status == 'published')
            <span class="text-green-500">
              <!-- Heroicon name: solid/check -->
              <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </span>
            @endif
          </div>
          <!-- Highlighted: "text-indigo-200", Not Highlighted: "text-gray-500" -->
          <p class="mt-2 text-left text-gray-500">
            {{ __('adminhub::partials.products.status-bar.published.description')}}
          </p>
        </button>
      </li>

      <li class="relative p-4 text-sm text-gray-900 cursor-default select-none @if($product->status != 'draft') hover:bg-gray-100 @endif" id="listbox-option-0" role="option">
        <button type="button" class="flex flex-col cursor-pointer" wire:click.prevent="$set('product.status', 'draft')">
          <div class="flex justify-between ">
            <!-- Selected: "font-semibold", Not Selected: "font-normal" -->
            <p class="@if($product->status == 'draft') font-semibold @else font-normal @endif">
              {{ __('adminhub::partials.products.status-bar.draft.label')}}
            </p>
            <!--
              Checkmark, only display for selected option.

              Highlighted: "text-white", Not Highlighted: "text-indigo-500"
            -->
            @if($product->status == 'draft')
            <span class="text-green-500">
              <!-- Heroicon name: solid/check -->
              <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </span>
            @endif
          </div>
          <!-- Highlighted: "text-indigo-200", Not Highlighted: "text-gray-500" -->
          <p class="mt-2 text-left text-gray-500">
            {{ __('adminhub::partials.products.status-bar.draft.description')}}
          </p>
        </button>
      </li>

      <!-- More items... -->
    </ul>
  </div>
</div>
