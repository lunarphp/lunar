<div>
  <div>
    <span>Exclude Products</span>
    <p class="text-sm text-gray-500">Choose which exlusion lists you want applied to this shipping method</p>
  </div>

  <div class="mt-4 space-y-2 max-h-96 overflow-y-auto">
    @foreach($this->exclusionLists as $list)
      <label
        for="list_{{ $list->id }}"
        wire:key="list_{{ $list->id }}"
        class="flex items-center p-2 space-x-2 border rounded cursor-pointer hover:bg-gray-50 text-sm"
      >
        <div class="flex items-center">
          <input id="list_{{ $list->id }}" type="checkbox" wire:model.defer="lists" value="{{ $list->id }}">
        </div>
        <div>
          {{ $list->name }}
        </div>
      </label>
    @endforeach
  </div>
</div>
