<?php

namespace Lunar\Base;

use Lunar\Base\DataTransferObjects\RefundAuthorizationResult;
use Lunar\Base\DataTransferObjects\RefundRequest;

class RefundAuthorization implements RefundAuthorizationInterface
{
    public function authorize(RefundRequest $refundRequest): RefundAuthorizationResult
    {
        return new RefundAuthorizationResult(
            authorized: true,
        );
    }
}
