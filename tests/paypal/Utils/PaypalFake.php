<?php

namespace Lunar\Tests\Paypal\Utils;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Lunar\Core\Models\Cart;
use Lunar\Paypal\Managers\PaypalManager;

/**
 * Fakes the PayPal REST endpoints the driver talks to, served from the recorded
 * responses in `packages/paypal/resources/responses`. Every fixture is keyed by
 * filename so a test reads as "this endpoint returns that recorded response".
 */
class PaypalFake
{
    /**
     * Register HTTP fakes for the given endpoint fixtures.
     *
     * `$responses` maps a URL pattern to either a fixture name, or a
     * `[fixture, status]` pair when the endpoint should fail.
     *
     * @param  array<string, string|array{0: string, 1: int}>  $responses
     */
    public static function fake(array $responses = []): void
    {
        $fakes = [
            '*/v1/oauth2/token' => Http::response(static::fixture('oauth_token')),
        ];

        foreach ($responses as $pattern => $response) {
            [$fixture, $status] = is_array($response) ? $response : [$response, 200];

            $fakes[$pattern] = Http::response(static::fixture($fixture), $status);
        }

        Http::fake($fakes);
    }

    /**
     * Fake an approved PayPal order (and its capture) sized to a cart.
     *
     * The recorded fixtures carry a fixed amount and currency; a cart's currency
     * code is generated, so tests that exercise the amount guard need the PayPal
     * side to agree with the cart unless they are deliberately making it differ.
     *
     * @param  array{amount?: string, currency?: string, status?: string, capture_status?: string, authorize?: bool}  $overrides
     */
    public static function forCart(Cart $cart, array $overrides = []): void
    {
        $cart = $cart->total ? $cart : $cart->calculate();

        $amount = $overrides['amount'] ?? PaypalManager::toPaypalAmount($cart->total->value, $cart->currency);
        $currency = $overrides['currency'] ?? $cart->currency->code;

        $approved = static::fixture('order_approved');
        $approved['status'] = $overrides['status'] ?? $approved['status'];
        $approved['purchase_units'][0]['amount'] = [
            'currency_code' => $currency,
            'value' => $amount,
        ];

        $captured = static::fixture('order_captured');
        $captured['status'] = $overrides['capture_status'] ?? $captured['status'];
        $capture = &$captured['purchase_units'][0]['payments']['captures'][0];
        $capture['amount'] = [
            'currency_code' => $currency,
            'value' => $amount,
        ];
        $capture['status'] = $captured['status'] === 'COMPLETED' ? 'COMPLETED' : $captured['status'];
        unset($capture);

        $authorized = static::fixture('order_authorized');
        $authorized['purchase_units'][0]['payments']['authorizations'][0]['amount'] = [
            'currency_code' => $currency,
            'value' => $amount,
        ];

        $authorizationCapture = static::fixture('authorization_captured');
        $authorizationCapture['amount'] = [
            'currency_code' => $currency,
            'value' => $amount,
        ];

        // The more specific patterns have to be registered first — Http::fake()
        // returns the first matching stub, and `*\/v2/checkout/orders/*` would
        // otherwise swallow the capture and authorize calls.
        Http::fake([
            '*/v1/oauth2/token' => Http::response(static::fixture('oauth_token')),
            '*/v2/checkout/orders/*/capture' => Http::response($captured),
            '*/v2/checkout/orders/*/authorize' => Http::response($authorized),
            '*/v2/checkout/orders/*' => Http::response($approved),
            '*/v2/payments/authorizations/*/capture' => Http::response($authorizationCapture),
            '*/v2/payments/captures/*/refund' => Http::response(static::fixture('refund')),
        ]);
    }

    /**
     * Read a recorded response as an array.
     *
     * @return array<string, mixed>
     */
    public static function fixture(string $name): array
    {
        $path = dirname(__DIR__, 3).'/packages/paypal/resources/responses/'.$name.'.json';

        if (! is_file($path)) {
            throw new \InvalidArgumentException("No recorded PayPal response named [{$name}].");
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * The decoded JSON body of the first recorded request matching a URL pattern.
     *
     * @return array<string, mixed>|null
     */
    public static function sentBody(string $pattern): ?array
    {
        $request = collect(Http::recorded())
            ->map(fn (array $pair): Request => $pair[0])
            ->first(fn (Request $request): bool => Str::is($pattern, $request->url()));

        return $request ? json_decode($request->body(), true) : null;
    }
}
