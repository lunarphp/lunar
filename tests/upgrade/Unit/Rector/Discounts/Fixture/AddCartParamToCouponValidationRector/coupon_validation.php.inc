<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Discounts\Fixture\AddCartParamToCouponValidationRector;

use Lunar\Core\Contracts\CouponValidator;
use Lunar\Core\Managers\DiscountManager;

class CustomCouponValidator implements CouponValidator
{
    public function validate(string $coupon): bool
    {
        return $coupon !== '';
    }
}

class LegacyCouponValidator implements \Lunar\Base\Validation\CouponValidatorInterface
{
    public function validate(string $coupon): bool
    {
        return true;
    }
}

class CustomDiscountManager extends DiscountManager
{
    public function validateCoupon(string $coupon): bool
    {
        return parent::validateCoupon($coupon);
    }
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Discounts\Fixture\AddCartParamToCouponValidationRector;

use Lunar\Core\Contracts\CouponValidator;
use Lunar\Core\Managers\DiscountManager;

class CustomCouponValidator implements CouponValidator
{
    public function validate(string $coupon, ?\Lunar\Core\Models\Cart $cart = null): bool
    {
        return $coupon !== '';
    }
}

class LegacyCouponValidator implements \Lunar\Base\Validation\CouponValidatorInterface
{
    public function validate(string $coupon, ?\Lunar\Core\Models\Cart $cart = null): bool
    {
        return true;
    }
}

class CustomDiscountManager extends DiscountManager
{
    public function validateCoupon(string $coupon, ?\Lunar\Core\Models\Cart $cart = null): bool
    {
        return parent::validateCoupon($coupon);
    }
}

?>
