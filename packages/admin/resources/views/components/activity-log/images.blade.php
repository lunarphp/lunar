<div>
  <div class="grid items-center grid-cols-6 gap-4">
  @foreach ($images as $image)
    @if($image['media'])
      <img src="{{ $image['media']->getFullUrl('small') }}" class="rounded shadow-sm">
    @else
      <div>
        <figure class="justify-center w-full bg-gray-100 rounded">
          <span class="block text-xs text-center text-gray-400">
              {{ __('adminhub::notifications.image.deleted') }}
          </span>
        </figure>
      </div>
    @endif
  @endforeach
  </div>
</div>
