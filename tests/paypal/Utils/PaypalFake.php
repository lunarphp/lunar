<?php

namespace Lunar\Tests\Paypal\Utils;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
