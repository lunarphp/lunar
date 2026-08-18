<?php

namespace Lunar\Panel\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\PanelManager;

/**
 * Shared endpoints behind every resource's draft routes. Sections register
 * the three routes against their own model binding; the bound record's
 * registered DraftableResource supplies the per-resource behaviour. All
 * endpoints operate on the current staff member's own draft only.
 */
class EditDraftController
{
    public function update(Request $request, PanelManager $panel, DraftManager $drafts): JsonResponse
    {
        [$record, $resource] = $this->draftable($request, $panel);

        $data = $request->validate(['data' => ['present', 'array']])['data'];

        $draft = $drafts->merge($resource, $record, $this->staff($panel), $data);

        return response()->json([
            'data' => $draft?->data ?? (object) [],
            'updated_at' => $draft?->updated_at?->toJSON(),
        ]);
    }

    public function destroy(Request $request, PanelManager $panel, DraftManager $drafts): Response
    {
        [$record] = $this->draftable($request, $panel);

        $drafts->discard($record, $this->staff($panel));

        return response()->noContent();
    }

    public function commit(Request $request, PanelManager $panel, DraftManager $drafts): JsonResponse
    {
        [$record, $resource] = $this->draftable($request, $panel);

        $payload = $request->validate([
            'data' => ['sometimes', 'array'],
            'rebase' => ['sometimes', 'array'],
        ]);

        $result = $drafts->commit(
            $resource,
            $record,
            $this->staff($panel),
            $payload['data'] ?? [],
            $payload['rebase'] ?? [],
        );

        if (! $result->committed) {
            return response()->json(['conflicts' => $result->conflicts], 409);
        }

        $request->session()->flash('success', __('panel::drafts.flash_committed'));

        return response()->json(['committed' => true]);
    }

    /**
     * The bound draftable record and its registered resource definition: the
     * deepest route-bound Eloquent model (on nested routes like a product's
     * variant, the child is the draft target), 404 when nothing is bound or
     * no definition covers it.
     *
     * @return array{0: Model, 1: DraftableResource}
     */
    protected function draftable(Request $request, PanelManager $panel): array
    {
        $record = collect($request->route()?->parameters() ?? [])
            ->last(fn (mixed $parameter): bool => $parameter instanceof Model);

        abort_unless($record instanceof Model, 404);

        $resource = $panel->draftableFor($record);

        abort_unless((bool) $resource, 404);

        return [$record, $resource];
    }

    protected function staff(PanelManager $panel): Authenticatable
    {
        $staff = $panel->user();

        abort_unless((bool) $staff, 401);

        return $staff;
    }
}
