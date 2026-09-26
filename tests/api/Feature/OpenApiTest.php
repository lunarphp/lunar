<?php

use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\OpenApi\Document;
use Lunar\Api\OpenApi\Generator;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
});

/**
 * Every `$ref` in the document points at something that exists.
 *
 * @return array<int, string>
 */
function danglingReferences(array $document): array
{
    $dangling = [];

    $walk = function (mixed $node) use (&$walk, &$dangling, $document): void {
        if (! is_array($node)) {
            return;
        }

        if (isset($node['$ref']) && is_string($node['$ref'])) {
            $segments = explode('/', ltrim($node['$ref'], '#/'));
            $target = $document;

            foreach ($segments as $segment) {
                if (! is_array($target) || ! array_key_exists($segment, $target)) {
                    $dangling[] = $node['$ref'];

                    return;
                }

                $target = $target[$segment];
            }
        }

        foreach ($node as $child) {
            $walk($child);
        }
    };

    $walk($document);

    return array_values(array_unique($dangling));
}

test('the storefront document describes its resources, operations and grammar', function (): void {
    $response = $this->getJson('/api/storefront/v1/openapi.json')->assertOk();
    $document = $response->json();

    expect($document['openapi'])->toBe('3.1.0');
    expect($document['info'])->toMatchArray(['title' => 'Storefront API', 'version' => 'v1']);
    expect($document['info'])->toHaveKey('x-lunar-release');
    expect($document['servers'][0]['url'])->toEndWith('/api/storefront/v1');
    expect($document['security'])->toBe([]);

    // Tags: one per resource, in registration order, with the label as the display group.
    expect(collect($document['tags'])->pluck('name')->take(3)->all())->toBe(['products', 'variants', 'product-option-values']);
    expect(collect($document['tags'])->firstWhere('name', 'collection-groups')['x-group'])->toBe('Collection groups');

    // Operations follow the conventions.
    $index = $document['paths']['/products']['get'];
    expect($index)->toMatchArray(['operationId' => 'productsIndex', 'summary' => 'List products', 'tags' => ['products']]);

    $parameters = collect($index['parameters']);
    expect($parameters->pluck('name')->filter()->all())->toContain('include', 'fields', 'filter', 'sort', 'page[number]', 'page[size]');
    expect($parameters->pluck('$ref')->filter()->all())->toContain('#/components/parameters/XLunarChannel', '#/components/parameters/XLunarCart');

    $include = $parameters->firstWhere('name', 'include');
    expect($include['x-lunar-includes'])->toContain('brand', 'variants', 'variants.values', 'collections.group');
    expect($include['x-lunar-includes'])->not->toContain('collections.children.children.children');

    $fields = $parameters->firstWhere('name', 'fields');
    expect($fields['style'])->toBe('deepObject');
    expect($fields['schema']['properties'])->toHaveKeys(['products', 'brands', 'variants', 'collections']);

    $filter = $parameters->firstWhere('name', 'filter');
    expect($filter['schema']['properties']['price']['oneOf'][0])->toBe(['type' => 'integer']);
    expect($filter['schema']['properties']['price']['oneOf'][1]['properties'])->toHaveKeys(['eq', 'gt', 'gte', 'lt', 'lte']);
    expect($filter['schema']['properties']['brand']['oneOf'][1]['properties']['in']['oneOf'][0])->toBe(['type' => 'array', 'items' => ['type' => 'string']]);
    expect($filter['schema']['properties']['id'])->toHaveKey('oneOf');

    expect($parameters->firstWhere('name', 'sort')['x-lunar-sorts'])->toBe(['created_at', 'name']);
    expect($parameters->firstWhere('name', 'page[size]')['schema'])->toMatchArray(['maximum' => 100, 'default' => 15]);

    $show = $document['paths']['/products/{id}']['get'];
    expect($show['summary'])->toBe('Retrieve a product');
    expect(collect($show['parameters'])->pluck('name')->filter()->all())->toBe(['id', 'include', 'fields']);
    expect($show['responses'])->toHaveKeys(['200', '404', '422', '429']);
    expect($show['responses'])->not->toHaveKey('401');
    expect($show['responses']['200']['content']['application/json']['schema']['properties']['data'])->toBe(['$ref' => '#/components/schemas/Product']);
    expect($show['responses']['200']['headers'])->toHaveKeys(['X-Lunar-Cart', 'Content-Language']);

    // Paginated envelopes carry the storefront meta and pagination.
    $envelope = $index['responses']['200']['content']['application/json']['schema'];
    expect($envelope['properties']['data'])->toBe(['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Product']]);
    expect($envelope['properties']['meta']['properties'])->toHaveKeys(['channel', 'currency', 'locale', 'pagination']);
    expect($envelope['required'])->toBe(['data', 'meta', 'links']);

    // Attribute-described write endpoint with a rule-derived body.
    $store = $document['paths']['/cart/lines']['post'];
    expect($store)->toMatchArray(['operationId' => 'cartsLinesStore', 'summary' => 'Add a line to the cart']);
    $body = $store['requestBody']['content']['application/json']['schema'];
    expect($body['required'])->toBe(['purchasable_id']);
    expect($body['properties']['quantity'])->toMatchArray(['type' => 'integer', 'minimum' => 1, 'description' => 'Units to add; defaults to 1.']);
    expect($body['properties']['meta']['type'])->toBe(['array', 'null']);
    expect($store['responses'])->toHaveKeys(['201', '422', '429']);

    expect($document['paths']['/cart']['get']['summary'])->toBe('Retrieve the current cart');
    expect($document['paths'])->not->toHaveKey('/me');
    expect($document['paths']['/openapi.json']['get']['x-hidden'])->toBeTrue();

    // Resource schemas: typed fields, nullability as required-ness, includes as references.
    $product = $document['components']['schemas']['Product'];
    expect($product['properties']['name'])->toMatchArray(['type' => 'string']);
    expect($product['properties']['price']['oneOf'])->toBe([['$ref' => '#/components/schemas/Money'], ['type' => 'null']]);
    expect($product['properties']['created_at'])->toMatchArray(['type' => 'string', 'format' => 'date-time']);
    expect($product['properties']['variants'])->toMatchArray(['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Variant']]);
    expect($product['properties']['brand']['oneOf'][0])->toBe(['$ref' => '#/components/schemas/Brand']);
    expect($product['required'])->toContain('id', 'type', 'name', 'created_at')->not->toContain('slug', 'price', 'brand', 'variants');
    expect($product['properties']['type']['const'])->toBe('products');

    expect($document['components']['schemas']['Variant']['properties']['selling_policy']['enum'])->toContain('always', 'in_stock');
    expect($document['components']['schemas']['Cart']['properties']['lines'])->toMatchArray(['type' => 'array', 'items' => ['$ref' => '#/components/schemas/CartLine']]);
    expect($document['components']['schemas'])->toHaveKeys(['Money', 'Error', 'ErrorResponse', 'PaginationMeta', 'Links', 'CollectionGroup', 'ProductOptionValue', 'Customer']);
    expect($document['components']['schemas'])->not->toHaveKey('TranslationMap');
    expect($document['components']['responses'])->not->toHaveKeys(['Unauthorized', 'Forbidden']);
    expect($document['components']['securitySchemes'] ?? [])->toBe([]);

    // Structural: every $ref resolves and every operation has a summary and an operationId.
    expect(danglingReferences($document))->toBe([]);

    foreach ($document['paths'] as $path => $operations) {
        foreach ($operations as $method => $operation) {
            expect($operation)->toHaveKeys(['operationId', 'summary', 'responses'], "{$method} {$path}");
            expect(isset($operation['tags']) || ($operation['x-hidden'] ?? false))->toBeTrue("{$method} {$path}");
        }
    }

    expect(collect($document['tags'])->pluck('name'))->not->toContain('openapi');

    // Empty schemas serialise as objects, not arrays.
    expect($response->getContent())->not->toContain('"items":[]')->not->toContain('"schema":[]')->not->toContain('"properties":[]');
});

test('the admin document is authenticated, applies the bearer scheme globally and marks ability gates', function (): void {
    $this->getJson('/api/admin/v1/openapi.json')->assertUnauthorized();

    $document = $this->withHeaders($this->apiKey(['catalog:read'])['headers'])->getJson('/api/admin/v1/openapi.json')->assertOk()->json();

    expect($document['info']['title'])->toBe('Admin API');
    expect($document['security'])->toBe([['apiKey' => []]]);
    expect($document['components']['securitySchemes']['apiKey'])->toMatchArray(['type' => 'http', 'scheme' => 'bearer']);
    expect($document['components'])->not->toHaveKeys(['parameters', 'headers']);

    $index = $document['paths']['/products']['get'];
    expect($index['x-lunar-requires'])->toBe(['catalog:read']);
    expect($index['responses'])->toHaveKeys(['200', '401', '403', '422', '429']);
    expect($index['responses']['200']['content']['application/json']['schema']['properties']['meta']['properties'])->toHaveKeys(['pagination']);

    // Translatable fields are locale maps on the admin surface.
    expect($document['components']['schemas']['Product']['properties']['name'])->toMatchArray(['$ref' => '#/components/schemas/TranslationMap']);

    // API keys: label override, hand-described body and bespoke success schema.
    expect(collect($document['tags'])->firstWhere('name', 'api-keys')['x-group'])->toBe('API keys');
    expect($document['paths']['/api-keys']['get']['summary'])->toBe('List API keys');
    expect($document['paths']['/api-keys/{id}']['get']['summary'])->toBe('Retrieve an API key');

    $store = $document['paths']['/api-keys']['post'];
    expect($store['summary'])->toBe('Issue an API key');
    expect($store['requestBody']['content']['application/json']['schema']['required'])->toBe(['name', 'abilities']);
    expect($store['responses']['201']['content']['application/json']['schema']['properties']['data']['allOf'][0])->toBe(['$ref' => '#/components/schemas/ApiKey']);

    $destroy = $document['paths']['/api-keys/{id}']['delete'];
    expect($destroy)->toMatchArray(['operationId' => 'apiKeysDestroy', 'summary' => 'Revoke an API key']);
    expect($destroy['responses'])->toHaveKeys(['204', '401', '403', '404', '429']);
    expect($destroy['responses']['204'])->not->toHaveKey('content');

    expect($document['components']['schemas'])->toHaveKey('TranslationMap');
    expect($document['components']['responses'])->toHaveKeys(['Unauthorized', 'Forbidden', 'NotFound', 'UnprocessableEntity', 'TooManyRequests']);
    expect(danglingReferences($document))->toBe([]);

    foreach ($document['paths'] as $path => $operations) {
        foreach ($operations as $method => $operation) {
            expect($operation)->toHaveKeys(['operationId', 'summary', 'responses'], "{$method} {$path}");
        }
    }
});

test('the command prints or writes the document and rejects an unknown surface', function (): void {
    $this->artisan('lunar:api:openapi', ['surface' => 'storefront'])
        ->expectsOutputToContain('"title": "Storefront API"')
        ->assertSuccessful();

    $path = sys_get_temp_dir().'/lunar-openapi-'.uniqid().'/admin.json';

    $this->artisan('lunar:api:openapi', ['surface' => 'admin', '--out' => $path])->assertSuccessful();

    $written = json_decode((string) file_get_contents($path), true);
    expect($written['info']['title'])->toBe('Admin API');
    expect($written['paths'])->toHaveKey('/api-keys');
    unlink($path);

    $this->artisan('lunar:api:openapi', ['surface' => 'storefront', '--yaml' => true])
        ->expectsOutputToContain("title: 'Storefront API'")
        ->assertSuccessful();

    $this->artisan('lunar:api:openapi', ['surface' => 'warehouse'])->assertFailed();
});

test('document taps run last and the document is memoised until the registry changes', function (): void {
    $registry = app(ApiManager::class)->storefront('v1');
    $generator = app(Generator::class);

    $first = $generator->generate($registry);
    expect($generator->generate($registry))->toBe($first);

    $registry->tapDocument(function (Document $document): void {
        $document->set('info.x-lunar-tapped', true);
        $document->set(['paths', '/products', 'get', 'x-mint'], ['content' => 'Extra prose.']);
        $document->set('servers', [['url' => 'https://store.example/api/storefront/v1', 'description' => 'Production']]);
    });

    $tapped = $generator->generate($registry);

    expect($tapped)->not->toBe($first);
    expect($tapped->get('info.x-lunar-tapped'))->toBeTrue();
    expect($tapped->get(['paths', '/products', 'get', 'x-mint', 'content']))->toBe('Extra prose.');
    expect($tapped->toArray()['servers'][0]['url'])->toBe('https://store.example/api/storefront/v1');
    expect($tapped->paths())->toHaveKey('/products');
    expect($tapped->tags())->not->toBeEmpty();
    expect($tapped->components())->toHaveKey('schemas');
});

test('configured servers replace the derived one', function (): void {
    config()->set('lunar.api.storefront.servers', [
        ['url' => 'https://a.example/api/storefront/v1', 'description' => 'A'],
        ['url' => 'https://b.example/api/storefront/v1'],
    ]);

    $document = $this->getJson('/api/storefront/v1/openapi.json')->assertOk()->json();

    expect($document['servers'])->toBe([
        ['url' => 'https://a.example/api/storefront/v1', 'description' => 'A'],
        ['url' => 'https://b.example/api/storefront/v1'],
    ]);
});
