<div class="px-4 pb-24 space-y-6 md:px-12">
  @include('adminhub::partials.forms.product-type')
  <div class="fixed bottom-0 left-0 right-0 z-50 p-6 mr-0 text-right bg-white bg-opacity-75 border-t md:left-64">
    <div class="flex justify-end w-full space-x-6">
      <form action="#" method="POST" wire:submit.prevent="create">
        <x-hub::button type="submit">{{ __('adminhub::catalogue.product-types.create.btn_text') }}</x-hub::button>
      </form
    </div>
  </div>
</div>
