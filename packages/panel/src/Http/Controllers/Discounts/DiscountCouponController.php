<?php

namespace Lunar\Panel\Http\Controllers\Discounts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Discounts\CreatesCoupon;
use Lunar\Core\Contracts\Actions\Discounts\DeletesCoupon;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use Lunar\Panel\Http\Requests\Discounts\DiscountCouponGenerateRequest;
use Lunar\Panel\Http\Requests\Discounts\DiscountCouponRequest;
use Lunar\Panel\Support\CouponGenerations;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiscountCouponController
{
    public function store(DiscountCouponRequest $request, Discount $discount, CreatesCoupon $createsCoupon): RedirectResponse
    {
        $createsCoupon->execute($discount, $request->validated());

        return back()->with('success', __('panel::discounts.flash_coupon_added'));
    }

    public function generate(DiscountCouponGenerateRequest $request, Discount $discount, CouponGenerations $generations): RedirectResponse
    {
        $batch = $request->batch();

        if ($batch->quantity > (int) config('lunar.discounts.coupons.queue_threshold')) {
            $generations->queue($discount, $batch);

            return back()->with('success', __('panel::discounts.flash_coupons_queued', ['count' => number_format($batch->quantity)]));
        }

        $discount->generateCoupons($batch);

        return back()->with('success', __('panel::discounts.flash_coupons_generated', ['count' => number_format($batch->quantity)]));
    }

    /**
     * Every code, or one batch, as CSV for a mail merge. Streamed a chunk at a
     * time so a large campaign is never held in memory.
     */
    public function export(Request $request, Discount $discount): StreamedResponse
    {
        $batch = $request->string('batch')->trim()->value();

        $coupons = $discount->coupons()
            ->withMax('redemptions', 'redeemed_at')
            ->when($batch !== '', fn ($query) => $query->where('batch', $batch));

        $filename = Str::slug($discount->handle ?: $discount->name).'-coupons'.($batch !== '' ? '-'.Str::lower($batch) : '').'.csv';

        return response()->streamDownload(function () use ($coupons): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['code', 'email', 'uses', 'redeemed_at'], escape: '');

            foreach ($coupons->lazyById(1000) as $coupon) {
                /** @var Coupon $coupon */
                fputcsv($out, [
                    $coupon->code,
                    $coupon->email,
                    $coupon->uses,
                    $coupon->getAttribute('redemptions_max_redeemed_at'),
                ], escape: '');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroy(Discount $discount, Coupon $coupon, DeletesCoupon $deletesCoupon): RedirectResponse
    {
        $deletesCoupon->execute($coupon);

        return back()->with('success', __('panel::discounts.flash_coupon_deleted'));
    }
}
