<?php

namespace Lunar\Panel\Http\Controllers\Discounts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Discounts\DeletesDiscount;
use Lunar\Core\Contracts\Actions\Discounts\UpdatesDiscount;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Discount;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\Discounts\DiscountRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\AvailabilitySchema;
use Lunar\Panel\Support\CouponGenerations;
use Lunar\Panel\Support\DiscountDataSchema;
use Lunar\Panel\Support\DiscountTargetSchema;
use Lunar\Panel\Support\DiscountTypeSchema;
use Lunar\Panel\Support\TimelineActivity;
use Spatie\Activitylog\Models\Activity;

class DiscountEditController
{
    public function edit(
        Request $request,
        Discount $discount,
        PanelManager $panel,
        DraftManager $drafts,
        AvailabilitySchema $availabilitySchema,
        DiscountTypeSchema $typeSchema,
        DiscountDataSchema $dataSchema,
        DiscountTargetSchema $targetSchema,
        CouponGenerations $couponGenerations,
    ): Response {
        $staff = $panel->user();
        $draft = $staff ? $drafts->find($discount, $staff) : null;

        $activities = $discount->activities()
            ->with('causer')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Activity $activity) => TimelineActivity::toArray($activity));

        return Inertia::render('discounts/Edit', [
            'discount' => [
                'id' => $discount->id,
                'name' => $discount->name,
                'handle' => $discount->handle,
                'redemption' => $discount->redemption->value,
                'type' => $discount->type,
                'status' => $discount->status,
                'status_label' => __('panel::discounts.status_'.$discount->status),
                'starts_at' => $discount->starts_at,
                'ends_at' => $discount->ends_at,
                'priority' => (int) $discount->priority,
                'stop' => (bool) $discount->stop,
                'uses' => (int) $discount->uses,
                'max_uses' => $discount->max_uses,
                'max_uses_per_user' => $discount->max_uses_per_user,
                'data' => $dataSchema->toForm($discount->type, $discount->data ?? []) ?: (object) [],
                'created_at' => $discount->created_at,
                'updated_at' => $discount->updated_at,
            ],
            // The type schema, not a hardcoded ladder: an unregistered type
            // still resolves, to the raw JSON editor.
            'type' => $typeSchema->describe($discount->type),
            'typeRegistered' => $typeSchema->isRegistered($discount->type),
            'draft' => $draft ? [
                'data' => $draft->data,
                'updated_at' => $draft->updated_at,
            ] : null,
            'currencies' => Currency::query()
                ->orderByDesc('default')
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'decimal_places', 'default']),
            // Ids for the draft to track, and resolved rows for the chips to
            // render, so the page needs no second round trip to show a target.
            'targets' => $targetSchema->values($discount),
            'targetChips' => $targetSchema->chips($discount),
            'availability' => $availabilitySchema->rows(),
            'availabilityValues' => $availabilitySchema->values($discount) ?: (object) [],
            'activities' => $activities,
            'coupons' => $this->coupons($request, $discount),
            'couponSearch' => $request->string('coupons_q')->value(),
            'couponGenerations' => $couponGenerations->unfinished($discount),
            'couponQueueThreshold' => (int) config('lunar.discounts.coupons.queue_threshold'),
            'urls' => [
                'index' => route('panel.discounts.index'),
                'activityLog' => route('panel.settings.activity-log.index', ['subject_type' => $discount->getMorphClass()]),
                'update' => route('panel.discounts.update', $discount),
                'destroy' => route('panel.discounts.destroy', $discount),
                'draft' => route('panel.discounts.draft.update', $discount),
                'draftCommit' => route('panel.discounts.draft.commit', $discount),
                'targetSearch' => route('panel.discounts.targets.search', $discount),
                'couponStore' => route('panel.discounts.coupons.store', $discount),
                'couponGenerate' => route('panel.discounts.coupons.generate', $discount),
                'couponExport' => route('panel.discounts.coupons.export', $discount),
            ],
        ]);
    }

    /**
     * One page of the discount's codes, searchable by code and email. A
     * generated campaign can hold thousands, so they are never sent whole.
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    protected function coupons(Request $request, Discount $discount): LengthAwarePaginator
    {
        $term = $request->string('coupons_q')->trim()->value();

        return $discount->coupons()
            ->withMax('redemptions', 'redeemed_at')
            ->when($term !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('code', 'like', '%'.Str::upper($term).'%')
                ->orWhere('email', 'like', "%{$term}%")))
            ->latest('id')
            ->paginate(10, pageName: 'coupons_page')
            ->withQueryString()
            ->through(fn (Coupon $coupon) => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'uses' => $coupon->uses,
                'max_uses' => $coupon->max_uses,
                'email' => $coupon->email,
                'batch' => $coupon->batch,
                'last_redeemed_at' => $coupon->getAttribute('redemptions_max_redeemed_at'),
                '_actions' => [
                    'delete' => route('panel.discounts.coupons.destroy', [$discount, $coupon]),
                    'exportBatch' => $coupon->batch ? route('panel.discounts.coupons.export', [$discount, 'batch' => $coupon->batch]) : null,
                ],
            ]);
    }

    public function update(DiscountRequest $request, Discount $discount, UpdatesDiscount $updatesDiscount, DiscountDataSchema $dataSchema): RedirectResponse
    {
        $attributes = $request->validated();

        if (array_key_exists('data', $attributes)) {
            $attributes['data'] = $dataSchema->toStorage(
                $attributes['type'] ?? $discount->type,
                (array) $attributes['data'],
            );
        }

        $updatesDiscount->execute($discount, $attributes);

        return back()->with('success', __('panel::discounts.flash_updated'));
    }

    public function destroy(Discount $discount, DeletesDiscount $deletesDiscount): RedirectResponse
    {
        $deletesDiscount->execute($discount);

        return redirect()
            ->route('panel.discounts.index')
            ->with('success', __('panel::discounts.flash_deleted'));
    }
}
