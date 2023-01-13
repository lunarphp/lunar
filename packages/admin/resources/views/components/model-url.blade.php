<div>
    @if($url)
    <x-hub::button tag="a" href="{{ $url }}" target="_blank" theme="gray">
        {{ __('adminhub::components.model-url.'. ($preview ? 'preview' : 'view')) }}
    </x-hub::button>
    @endif
</div>
