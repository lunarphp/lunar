<div>
  @if(!count($getMailers()))
    <p class="text-sm text-grey-500 block text-center">
      {{ __('lunarpanel::components.forms.mailer-select.preview.empty') }}
    </p>
  @else
    <x-filament::input.wrapper>
      <x-filament::input.select wire:model="selectedMailer">
        @foreach($getMailers() as $value => $label)
          <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
      </x-filament::input.select>
    </x-filament::input.wrapper>

    <div class="mt-4">
      <iframe
              srcdoc="{{ $getPreview() }}"
              class="grow w-full h-full"
              style="height:500px"
      ></iframe>
    </div>
  @endif
</div>