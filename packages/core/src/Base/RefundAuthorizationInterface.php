<?php

namespace Lunar\Base;

use Lunar\Base\DataTransferObjects\RefundAuthorizationResult;
use Lunar\Base\DataTransferObjects\RefundRequest;

interface RefundAuthorizationInterface
{
    public function authorize(RefundRequest $refundRequest): RefundAuthorizationResult;
}
