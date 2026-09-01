<?php

namespace Lunar\Panel\Http\Controllers\Search;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Panel\PanelManager;

/**
 * The command palette's record search: one request fanned out across every
 * source the user may see, rather than a request per entity. Gating happens per
 * source, so this route needs no permission of its own.
 */
class GlobalSearchController
{
    /** How many rows each source contributes, so no one source crowds out the rest. */
    private const PER_SOURCE = 5;

    public function __invoke(Request $request, PanelManager $manager): JsonResponse
    {
        $resolver = $manager->resolveSearchSources();

        $kinds = array_values(array_filter(
            (array) $request->input('kinds', []),
            fn (mixed $kind): bool => is_string($kind),
        ));

        return response()->json([
            'data' => $resolver->search(
                $request->string('q')->value(),
                $kinds,
                self::PER_SOURCE,
            ),
        ]);
    }
}
