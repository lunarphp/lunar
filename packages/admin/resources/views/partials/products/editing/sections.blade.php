<div class="flex items-center space-x-4">
  {{--
    Product title.
   --}}
  <a href="{{ route('hub.products.index') }}" class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white" title="{{ __('adminhub::catalogue.products.show.back_link_title') }}">
    <x-hub::icon ref="chevron-left" style="solid" class="w-8 h-8" />
  </a>
  <h1 class="text-xl font-bold md:text-xl">
    @if($product->id)
      {{ $product->translateAttribute('name') }}
    @else
      New Product
    @endif
  </h1>
</div>

{{--
  Status bar.
 --}}
<form wire:submit.prevent="save" class="fixed bottom-0 right-0 z-50 p-6 mr-0 text-right bg-white bg-opacity-75 border-t left-64">
  <div class="flex justify-end w-full space-x-6">
    @include('adminhub::partials.products.status-bar')
    <x-hub::button type="submit">{{ __('adminhub::catalogue.products.show.save_btn') }}</x-hub::button>
  </div>
</form>

{{--
  Sections
 --}}
 <div class="py-12 pb-24 lg:grid lg:grid-cols-12 lg:gap-x-12">
  <div class="sm:px-6 lg:px-0 lg:col-span-9">
    <div class="space-y-6">
      <div>
        @if(!$this->hasChannelAvailability)
          <x-hub::alert level="danger">
            {{ __('adminhub::catalogue.products.show.no_channel_availability') }}
          </x-hub::alert>
        @endif
      </div>

      {{--
        Basic Information
       --}}
      <div id="basic-information">
        @include('adminhub::partials.products.editing.basic-information')
      </div>

      {{--
        Attributes
       --}}
      <div id="attributes">
        @include('adminhub::partials.attributes')
      </div>

      {{--
        Images
       --}}
       <div id="images">
        @include('adminhub::partials.image-manager', [
          'existing' => $images,
          'wireModel' => 'imageUploadQueue',
          'filetypes' => ['image/*'],
        ])
      </div>

      {{--
        Availability
       --}}
      <div id="availability">
        @include('adminhub::partials.availability', [
          'channels' => true,
          'customerGroups' => true,
        ])
      </div>

      {{--
        Variants
       --}}
      <div id="variants">
        @include('adminhub::partials.products.editing.variants')
      </div>

      @if($this->getVariantsCount() <= 1)
        <div id="pricing">
          @include('adminhub::partials.pricing')
        </div>
        <div id="identifiers">
          @include('adminhub::partials.products.variants.identifiers')
        </div>
        <div id="inventory">
          @include('adminhub::partials.products.variants.inventory')
        </div>
        <div id="shipping">
          @include('adminhub::partials.shipping')
        </div>
      @endif

      {{--
        URLs
       --}}
      <div id="urls">
        @include('adminhub::partials.urls')
      </div>

      {{--
        Collections
       --}}
       <div id="collections">
        @include('adminhub::partials.products.editing.collections')
       </div>

      {{--
        Delete area
       --}}
       @if($product->id)
        <div class="bg-white border border-red-300 rounded shadow">
          <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
            {{ __('adminhub::inputs.danger_zone.title') }}
          </header>
          <div class="p-6 text-sm">
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 lg:col-span-8">
                <strong>{{ __('adminhub::inputs.danger_zone.label', ['model' => 'product']) }}</strong>
                <p class="text-xs text-gray-600">
                  {{ __('adminhub::catalogue.products.show.delete_strapline') }}
                </p>
              </div>
              <div class="col-span-6 text-right lg:col-span-4">
                <x-hub::button :disabled="false" wire:click="$set('showDeleteConfirm', true)" type="button" theme="danger">
                  {{ __('adminhub::global.delete') }}
                </x-hub::button>
              </div>
            </div>
          </div>
        </div>
        <x-hub::modal.dialog wire:model="showDeleteConfirm">
          <x-slot name="title">
            {{ __('adminhub::catalogue.products.show.delete_title') }}
          </x-slot>

          <x-slot name="content">
            {{ __('adminhub::catalogue.products.show.delete_strapline') }}
          </x-slot>

          <x-slot name="footer">
            <div class="flex items-center justify-end space-x-4">
              <x-hub::button theme="gray" type="button" wire:click.prevent="$set('showDeleteConfirm', false)">
                {{ __('adminhub::global.cancel') }}
              </x-hub::button>
              <x-hub::button wire:click="delete" theme="danger">
                {{ __('adminhub::catalogue.products.show.delete_btn') }}
              </x-hub::button>
            </div>
          </x-slot>
        </x-hub::modal.dialog>
      @endif

      {{--
        Activity Log
       --}}

       <div class="pt-12 mt-12 border-t">
        @livewire('hub.components.activity-log-feed', [
          'subject' => $product,
        ])
      </div>

    </div>
  </div>
  <div>
    <aside class="fixed hidden px-2 py-6 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3 md:block">
      <nav class="space-y-2" aria-label="Sidebar">
        @foreach($this->sideMenu as $item)
        <a
          href="#{{ $item['id'] }}"
          class="@if(!empty($item['has_errors'])) text-red-600 @else text-gray-900 @endif flex items-center text-sm font-medium bg-gray-100 rounded-md hover:text-indigo-500 hover:underline group"
          aria-current="page"
        >
          @if(!empty($item['has_errors']))<x-hub::icon ref="exclamation-circle" class="w-4 mr-1 text-red-600" />@endif
          <span class="truncate">
            {{ $item['title'] }}
          </span>
        </a>
        @endforeach
      </nav>
    </aside>
  </div>
 </div>
