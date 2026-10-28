@props(['results'])
@php($searchId = $results->meta['search_id'] ?? null)
@if ($searchId)
<script data-lunar-search-tracking="{{ $searchId }}">
{!! lunar_search_tracking_script() !!}
LunarSearchRelevance.attach({ endpoint: @json(route('lunar.search-relevance.events')), token: @json(csrf_token()) });
</script>
@endif
