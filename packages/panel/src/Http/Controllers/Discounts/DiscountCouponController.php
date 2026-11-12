<?php

namespace Lunar\Panel\Http\Controllers\Discounts;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Discounts\CreatesCoupon;
use Lunar\Core\Contracts\Actions\Discounts\DeletesCoupon;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use Lunar\Panel\Http\Requests\Discounts\DiscountCouponRequest;

class DiscountCouponController
{
    public function store(DiscountCouponRequest $request, Discount $discount, CreatesCoupon $createsCoupon): RedirectResponse
    {
        $createsCoupon->execute($discount, $request->validated());

        return back()->with('success', __('panel::discounts.flash_coupon_added'));
    }

    public function destroy(Discount $discount, Coupon $coupon, DeletesCoupon $deletesCoupon): RedirectResponse
    {
        $deletesCoupon->execute($coupon);

        return back()->with('success', __('panel::discounts.flash_coupon_deleted'));
    }
}
