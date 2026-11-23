<?php

namespace Lunar\Panel\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Drafts\StaleDraftException;
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

        $payload = $request->validate([
            'data' => ['present', 'array'],
            ...$this->protocolRules(),
        ]);

        $staff = $this->staff($panel);

        try {
            $draft = $drafts->merge($resource, $record, $staff, $payload['data'], $payload['base'] ?? [], $payload['version'] ?? null);
        } catch (StaleDraftException $exception) {
            return $this->stale($exception);
        }

        return response()->json([
            'data' => $draft?->data ?? (object) [],
            'updated_at' => $draft?->updated_at?->toJSON(),
            'version' => (int) ($draft?->version ?? 0),
        ]);
    }

    public function destroy(Request $request, PanelManager $panel, DraftManager $drafts): JsonResponse|Response
    {
        [$record] = $this->draftable($request, $panel);

        $payload = $request->validate($this->protocolRules());

        try {
            $drafts->discard($record, $this->staff($panel), $payload['version'] ?? null);
        } catch (StaleDraftException $exception) {
            return $this->stale($exception);
        }

        return response()->noContent();
    }

    public function commit(Request $request, PanelManager $panel, DraftManager $drafts): JsonResponse
    {
        [$record, $resource] = $this->draftable($request, $panel);

        $payload = $request->validate([
            'data' => ['sometimes', 'array'],
            'rebase' => ['sometimes', 'array'],
            ...$this->protocolRules(),
        ]);

        $staff = $this->staff($panel);

        try {
            $result = $drafts->commit(
                $resource,
                $record,
                $staff,
                $payload['data'] ?? [],
                $payload['rebase'] ?? [],
                $payload['base'] ?? [],
                $payload['version'] ?? null,
            );
        } catch (StaleDraftException $exception) {
            return $this->stale($exception);
        } catch (ValidationException $exception) {
            // A refused commit keeps its edits drafted; the client needs the
            // new version to keep autosaving against it.
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
                'version' => $this->version($drafts, $record, $staff),
            ], 422);
        }

        if (! $result->committed) {
            return response()->json([
                'conflicts' => $result->conflicts,
                'version' => $this->version($drafts, $record, $staff),
            ], 409);
        }

        $request->session()->flash('success', __('panel::drafts.flash_committed'));

        return response()->json(['committed' => true, 'version' => 0]);
    }

    /**
     * Rules shared by every draft request: the base each newly drafted key
     * had on the client, and the draft version the client last saw.
     *
     * @return array<string, array<int, string>>
     */
    protected function protocolRules(): array
    {
        return [
            'base' => ['sometimes', 'array'],
            'version' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    protected function stale(StaleDraftException $exception): JsonResponse
    {
        return response()->json(['stale_draft' => true, 'version' => $exception->currentVersion], 409);
    }

    protected function version(DraftManager $drafts, Model $record, Authenticatable $staff): int
    {
        return (int) ($drafts->find($record, $staff)?->version ?? 0);
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
