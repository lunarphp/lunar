<?php

namespace Lunar\Api\OpenApi;

use Illuminate\Contracts\Config\Repository;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Resources\Translations;

/**
 * What differs between surfaces in the document: how translatable fields
 * serialise, which security scheme applies, the context headers and the
 * envelope meta. Unknown surfaces get the admin-like defaults.
 */
final class SurfaceProfile
{
    /**
     * @param  array<string, array<string, mixed>>  $requestHeaders  component parameters keyed by component name
     * @param  array<string, array<string, mixed>>  $responseHeaders  component headers keyed by header name
     * @param  array<string, array<string, mixed>>  $metaProperties  envelope `meta` properties
     * @param  array<string, array<string, mixed>>  $securitySchemes
     */
    public function __construct(
        public readonly string $surface,
        public readonly string $version,
        public readonly string $prefix,
        public readonly Translations $translations,
        public readonly array $requestHeaders = [],
        public readonly array $responseHeaders = [],
        public readonly array $metaProperties = [],
        public readonly array $securitySchemes = [],
        public readonly ?string $globalSecurityScheme = null,
        public readonly ?string $customerSecurityScheme = null,
    ) {}

    public static function for(SurfaceRegistry $registry, Repository $config): self
    {
        $prefix = trim((string) $config->get("lunar.api.{$registry->surface}.prefix", "api/{$registry->surface}"), '/')."/{$registry->version}";

        return match ($registry->surface) {
            'storefront' => self::storefront($registry, $prefix, (string) $config->get('lunar.api.storefront.guard', '')),
            'admin' => self::admin($registry, $prefix),
            default => new self($registry->surface, $registry->version, $prefix, Translations::Map),
        };
    }

    public function label(): string
    {
        return ucfirst($this->surface);
    }

    private static function storefront(SurfaceRegistry $registry, string $prefix, string $guard): self
    {
        $schemes = $guard !== '' ? [
            'customer' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'description' => 'A credential for the host guard named by `lunar.api.storefront.guard`. Required by the customer endpoints only.',
            ],
        ] : [];

        return new self(
            surface: $registry->surface,
            version: $registry->version,
            prefix: $prefix,
            translations: Translations::Resolved,
            requestHeaders: [
                'XLunarChannel' => [
                    'name' => 'X-Lunar-Channel',
                    'in' => 'header',
                    'required' => false,
                    'schema' => ['type' => 'string'],
                    'description' => 'Handle of the channel to serve. Defaults to the default channel; an unknown handle is rejected with 422.',
                ],
                'XLunarCurrency' => [
                    'name' => 'X-Lunar-Currency',
                    'in' => 'header',
                    'required' => false,
                    'schema' => ['type' => 'string'],
                    'description' => 'ISO 4217 code of the currency to price in. Defaults to the default currency; an unknown or disabled code is rejected with 422.',
                ],
                'XLunarCart' => [
                    'name' => 'X-Lunar-Cart',
                    'in' => 'header',
                    'required' => false,
                    'schema' => ['type' => 'string'],
                    'description' => 'The signed cart token a previous response returned on X-Lunar-Cart. An invalid or expired token is rejected with 401, a token for a cart that no longer exists with 404.',
                ],
                'AcceptLanguage' => [
                    'name' => 'Accept-Language',
                    'in' => 'header',
                    'required' => false,
                    'schema' => ['type' => 'string'],
                    'description' => 'Preferred languages. The best match among the store languages is used for translatable fields; otherwise the default language.',
                ],
            ],
            responseHeaders: [
                'X-Lunar-Channel' => ['schema' => ['type' => 'string'], 'description' => 'Handle of the channel the response was served for.'],
                'X-Lunar-Currency' => ['schema' => ['type' => 'string'], 'description' => 'ISO 4217 code of the currency prices are in.'],
                'Content-Language' => ['schema' => ['type' => 'string'], 'description' => 'The locale translatable fields were resolved in.'],
                'X-Lunar-Cart' => ['schema' => ['type' => 'string'], 'description' => 'A fresh signed token for the current cart. Present when the request had or created a cart; send it on the next cart request.'],
            ],
            metaProperties: [
                'channel' => ['type' => 'string', 'description' => 'Handle of the channel the response was served for.'],
                'currency' => ['type' => 'string', 'description' => 'ISO 4217 code of the currency prices are in.'],
                'locale' => ['type' => 'string', 'description' => 'The locale translatable fields were resolved in.'],
            ],
            securitySchemes: $schemes,
            customerSecurityScheme: $guard !== '' ? 'customer' : null,
        );
    }

    private static function admin(SurfaceRegistry $registry, string $prefix): self
    {
        return new self(
            surface: $registry->surface,
            version: $registry->version,
            prefix: $prefix,
            translations: Translations::Map,
            securitySchemes: [
                'apiKey' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'description' => 'An admin API key issued with `lunar:api:key create` or `POST /api-keys`, or a token of the guard named by `lunar.api.admin.guard`. Abilities gate endpoints and fields; see `x-lunar-requires`.',
                ],
            ],
            globalSecurityScheme: 'apiKey',
        );
    }
}
