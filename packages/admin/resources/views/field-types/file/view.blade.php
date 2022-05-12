<div>
  @if($field['data'] && is_array($field['data']))
    <div class="flex space-x-4 items-center">
      @if (!str_contains($field['data']['mime_type'], 'image'))
        <x-hub::button
          tag="a"
          href="{{ Storage::disk($field['data']['disk'])->url($field['data']['path']) }}"
          target="_blank"
          >
          Download
        </x-hub::button>
      @else
        <img src="{{ Storage::disk($field['data']['disk'])->url($field['data']['path']) }}" class="rounded-md w-24" />
      @endif


      <div>
        <x-hub::button type="button" size="sm" theme="gray" wire:click="$set('{{ $field['signature'] }}', null)">
          <x-hub::icon ref="trash" class="w-4" />
        </x-hub::button>
      </div>
    </div>
  @else
    <input type="file" wire:model="{{ $field['signature'] }}" />
  @endif
  {{-- @if(($field['configuration']['richtext'] ?? false))
    <x-hub::input.richtext
      id="{{ $field['id'] }}"
      wire:model.defer="{{ $field['signature'] }}{{ isset($language) ? '.' . $language : null }}"
      :initial-value="isset($language) ? ($field['data'][$language] ?? null) : $field['data']"
    />
  @else
    <x-hub::input.text wire:model="{{ $field['signature'] }}{{ isset($language) ? '.' . $language : null }}" />
  @endif --}}
</div>
