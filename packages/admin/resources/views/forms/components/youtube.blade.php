<div class="space-y-4">
    @include('filament-forms::components.text-input')
    @if($getState() != '')
        <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $getState() }}?si=IbDk-LmI4_qUTaUN"
                title="YouTube video player" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen></iframe>
    @endif
</div>