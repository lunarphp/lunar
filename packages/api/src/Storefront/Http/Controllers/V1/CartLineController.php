<?php

namespace Lunar\Api\Storefront\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Lunar\Api\Http\Responses\Envelope;
use Lunar\Api\Storefront\Http\Requests\V1\StoreCartLineRequest;
use Lunar\Api\Storefront\Resources\V1\CartResource;
use Lunar\Api\Storefront\Resources\V1\ProductResource;
use Lunar\Core\Contracts\LunarUser;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\ProductVariant;

class CartLineController extends Controller
{
    protected string $resource = CartResource::class;

    /**
     * Add a purchasable to the cart, creating the cart when the request
     * carries none. The new cart's token comes back on `X-Lunar-Cart`.
     */
    public function store(StoreCartLineRequest $request): JsonResponse
    {
        $context = $this->context($request);

        $variant = ProductVariant::query()
            ->where('enabled', true)
            ->wherePublicId($request->validated('purchasable_id'))
            ->whereHas('product', fn ($product) => ProductResource::visible($product, $context))
            ->first();

        if (! $variant) {
            throw ValidationException::withMessages([
                'purchasable_id' => __('api::validation.purchasable_not_found'),
            ]);
        }

        $cart = $this->cartSession()->current() ?? $this->createCart($request->user());

        $cart->add($variant, (int) $request->validated('quantity', 1), $request->validated('meta', []) ?? []);

        return Envelope::item(
            $this->definition()->serialize($this->cartSession()->current(), $context),
            $this->meta($request, $context),
            [],
            201,
        );
    }

    protected function createCart(mixed $user): Cart
    {
        $cart = Cart::query()->create([
            'currency_id' => $this->storefront()->getCurrency()->id,
            'channel_id' => $this->storefront()->getChannel()->id,
            'region_id' => $this->storefront()->getRegion()?->id,
            'customer_id' => $this->storefront()->getCustomer()?->id,
            'user_id' => $user instanceof LunarUser ? $user->getAuthIdentifier() : null,
        ]);

        return $this->cartSession()->use($cart);
    }
}
