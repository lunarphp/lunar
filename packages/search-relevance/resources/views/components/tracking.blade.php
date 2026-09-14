@props(['results'])
@php($searchId = $results->meta['search_id'] ?? null)
@if ($searchId)
<script data-lunar-search-tracking="{{ $searchId }}">
(function () {
    var endpoint = @json(route('lunar.search-relevance.events'));
    var token = @json(csrf_token());
    document.addEventListener('click', function (event) {
        var target = event.target && event.target.closest ? event.target.closest('[data-lunar-search-id]') : null;
        if (! target) { return; }
        var data = new FormData();
        data.append('search_id', target.getAttribute('data-lunar-search-id'));
        data.append('product_id', target.getAttribute('data-lunar-product-id'));
        data.append('position', target.getAttribute('data-lunar-position'));
        data.append('source', target.getAttribute('data-lunar-source') || 'organic');
        data.append('_token', token);
        if (navigator.sendBeacon) {
            navigator.sendBeacon(endpoint, data);
        } else {
            fetch(endpoint, { method: 'POST', body: data, keepalive: true, credentials: 'same-origin' });
        }
    }, true);
})();
</script>
@endif
