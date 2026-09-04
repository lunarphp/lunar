<?php

namespace Lunar\Api\Http\Responses;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * The `data` / `meta` / `links` response shape. Related resources embed under
 * their parent rather than in a separate `included` array.
 */
final class Envelope
{
    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $links
     * @param  array<string, string>  $headers
     */
    public static function item(?array $data, array $meta = [], array $links = [], int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse(array_filter([
            'data' => $data,
            'meta' => $meta ?: null,
            'links' => $links ?: null,
        ], fn ($value, $key) => $key === 'data' || $value !== null, ARRAY_FILTER_USE_BOTH), $status, $headers);
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $links
     */
    public static function collection(array $data, array $meta = [], array $links = [], int $status = 200): JsonResponse
    {
        return new JsonResponse(array_filter([
            'data' => $data,
            'meta' => $meta ?: null,
            'links' => $links ?: null,
        ], fn ($value, $key) => $key === 'data' || $value !== null, ARRAY_FILTER_USE_BOTH), $status);
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @param  array<string, mixed>  $meta
     */
    public static function paginated(LengthAwarePaginator|CursorPaginator $paginator, array $data, Request $request, array $meta = []): JsonResponse
    {
        if ($paginator instanceof CursorPaginator) {
            $meta['pagination'] = [
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
            ];

            $links = [
                'self' => $request->fullUrl(),
                'next' => $paginator->nextCursor() ? self::pageUrl($request, ['cursor' => $paginator->nextCursor()->encode()]) : null,
                'prev' => $paginator->previousCursor() ? self::pageUrl($request, ['cursor' => $paginator->previousCursor()->encode()]) : null,
            ];

            return self::collection($data, $meta, $links);
        }

        $meta['pagination'] = [
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];

        $links = [
            'self' => $request->fullUrl(),
            'first' => self::pageUrl($request, ['number' => 1]),
            'last' => self::pageUrl($request, ['number' => $paginator->lastPage()]),
            'next' => $paginator->hasMorePages() ? self::pageUrl($request, ['number' => $paginator->currentPage() + 1]) : null,
            'prev' => $paginator->currentPage() > 1 ? self::pageUrl($request, ['number' => $paginator->currentPage() - 1]) : null,
        ];

        return self::collection($data, $meta, $links);
    }

    /** @param  array<string, mixed>  $page */
    private static function pageUrl(Request $request, array $page): string
    {
        $query = $request->query();
        $existing = is_array($query['page'] ?? null) ? Arr::except($query['page'], ['number', 'cursor']) : [];
        $query['page'] = array_merge($existing, $page);

        return $request->url().'?'.Arr::query($query);
    }
}
