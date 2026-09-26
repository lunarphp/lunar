<?php

namespace Lunar\Api\Storefront\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lunar\Api\Http\Exceptions\ApiException;
use Lunar\Api\Http\Responses\Envelope;
use Lunar\Api\OpenApi\Attributes\Operation;
use Lunar\Api\OpenApi\Attributes\Responds;
use Lunar\Api\Storefront\Resources\V1\CustomerResource;

class MeController extends Controller
{
    protected string $resource = CustomerResource::class;

    /** The authenticated user's customer record. */
    #[Operation(summary: 'Retrieve the authenticated customer', description: 'The customer record of the user authenticated by the host guard. Responds 404 when the user has no customer record yet.')]
    #[Responds(CustomerResource::class)]
    public function me(Request $request): JsonResponse
    {
        $customer = $this->storefront()->getCustomer()
            ?? throw ApiException::make(404, 'customer_not_found');

        $context = $this->context($request);

        return Envelope::item(
            $this->definition()->serialize($customer, $context),
            $this->meta($request, $context),
            ['self' => $request->fullUrl()],
        );
    }
}
