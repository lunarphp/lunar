# 0081 — OpenAPI documents generated from the API registry

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-09-06
- TODO item: API platform — OpenAPI documents for the storefront and admin surfaces (follow-on to spec 0077)

## Problem

Spec 0077 ships a `_schema` endpoint and `lunar:api:schema` that list every registered resource with its fields, includes, filters, sorts and routes. That output is enough for a human to see what a surface serves and nothing else:

- **No shapes.** A field is a name plus a translatable flag. Filters carry operators but not the type of the value. Nothing says whether `price` is a `Money` object or an integer, or whether `brand_id` may be null. A frontend developer who wants a typed client from Orval, openapi-typescript or Kiota has to write the service and its types by hand, which is the work the API was supposed to remove.
- **No requests or responses.** `_schema` knows the routes but not the request body `POST /cart/lines` accepts, nor that `GET /products` returns a paginated envelope of products and `GET /cart` an item envelope of a cart. Error shapes are undocumented.
- **No prose.** There is nowhere to say what an endpoint does or what a field means. Mintlify renders API pages from an OpenAPI document and needs a `summary` per operation, a `description` per operation, property and parameter, and tags to group pages; none of that exists.
- **Wrong audience.** `_schema` filters by the caller's abilities. That is right for a runtime "what can I see" call and wrong for code generation, where the client needs every field the surface can ever return and a marker saying which ability gates it.
- **Add-ons are invisible to any hand-written document.** The registry merges `ResourceExtension` fields, filters, embeds and routes at boot. A static document, or one produced by a scanner that reads controllers and `JsonResource` classes, would miss all of them and go stale on every add-on install.

## Proposal

Generate an OpenAPI 3.1 document per surface and version from the `SurfaceRegistry`, replacing `_schema` and `lunar:api:schema`. The registry is already the single merged source of truth, so a document built from it describes built-in and add-on resources the same way. The kernel gains the three things the registry lacks: **types**, **descriptions**, and **operation metadata**. Everything else (paths, parameters, tags, envelopes, errors, security) is derived.

### A. Types

`Lunar\Api\OpenApi\Schema` is an immutable value class that emits a JSON Schema fragment. It covers the scalar and structural types the surfaces return and the domain shapes the `Normalizer` produces:

```php
Schema::string()            Schema::integer()         Schema::number()      Schema::boolean()
Schema::dateTime()          Schema::date()            Schema::any()
Schema::enum(Status::class) Schema::enum(['draft', 'published'])
Schema::array(Schema $items)
Schema::object(['handle' => Schema::string(), ...], required: [...])
Schema::map(Schema $values)                          // additionalProperties
Schema::money()                                      // component $ref: Money
Schema::translations()                               // component $ref: TranslationMap
Schema::ref(BrandResource::class)                    // component $ref to a resource schema
->nullable()  ->describe(string)  ->example(mixed)  ->format(string)  ->constrain([...])
```

`Field::nullable()` marks a field's declared or inferred type as accepting null, so `Field::make('gtin')->nullable()` keeps cast inference and `Field::translatable('description')->nullable()` stays a string or `TranslationMap` per surface. A field is `required` in its resource schema unless its type is nullable.

Declaring types:

- `Field::make(...)->type(Schema $schema)` and `Filter::...->type(Schema $schema)`. The filter type describes the value; operators are already declared.
- `Embed` needs no type: the target resource is known, so it emits `$ref` to that resource's schema. Cardinality is read from the Eloquent relation on the resource's model at generation time (`HasMany`, `BelongsToMany`, `MorphMany`, `HasManyThrough` are arrays; the rest are single). `Embed::make()` with a closure declares `->many()` explicitly, defaulting to single.
- `Field::translatable()` needs no type: it emits `string` on surfaces whose context uses `Translations::Resolved` and `TranslationMap` on surfaces using `Translations::Map`.
- `Field::make('name')` with no closure infers from the model's casts (`datetime` and `immutable_datetime` to `date-time`, `int`/`integer` to integer, `bool`/`boolean` to boolean, `array`/`json`/`collection` to `any`, `decimal:N` and `float` to number, enum casts to `enum`, `Price` cast to `Money`, everything else to string). Casts are read from a fresh model instance of `Resource::model()`; the generator never touches the database.
- Every other field without a declared type emits `{}` with `x-lunar-untyped: true`. That is a degraded document, not a boot failure, so an add-on that forgets a type cannot take a store down. `tests/api/Unit/OpenApiCoverageTest.php` asserts every built-in field and filter across both surfaces is typed, so the shipped documents are never degraded.

Type declarations are a promise the closure cannot enforce. That is the same trade-off every hand-written document makes; the inference paths above shrink the surface where a field can lie to closure-resolved fields only.

### B. Descriptions and labels

Short text lives in code so add-ons can describe what they own. Long prose lives in the docs site (section H).

- `Resource::label(): string` defaults to the headline form of `type()` (`collection-groups` becomes "Collection groups"). `Resource::description(): string` defaults to empty. The label names the component schema and the tag's display name; the description is the tag description and the schema description.
- `Field`, `Filter`, `Embed` and `Sort` gain `describe(string $text)`. The text becomes the property or parameter `description`.
- Operations are described with attributes on controller methods, in `Lunar\Api\OpenApi\Attributes`:

  ```php
  #[Operation(summary: 'Add a line to the cart', description: 'Creates a cart when the request carries no cart token.')]
  #[Responds(CartResource::class, status: 201)]
  public function store(StoreCartLineRequest $request): JsonResponse
  ```

  `Operation` sets `summary` and `description`. `Responds` declares the success response: a resource class for an item envelope, `many: true` for a collection envelope (`paginated: true`, the default for `many`, adds pagination meta and links), `status` (default 200), or no resource with `status: 204` for an empty response. Responses that are not a plain resource, such as the one-time `token` on `POST /api-keys`, name a class implementing `Lunar\Api\Contracts\DescribesSchema` (`public static function schema(): Schema`) through `schema:`.
- `ResourceController::index()` and `show()` are described by convention when the method carries no attribute: "List {label}" with a paginated collection response and "Retrieve a {singular label}" with an item response. Built-in read endpoints therefore need no attributes.
- Form requests describe their body fields with an optional `descriptions(): array<string, string>` method keyed by field name, merged into the rule-derived body schema (section C).
- Operations under a route with `lunar.api.can:{ability}` middleware get `x-lunar-requires: [ability]` and a 403 response. Fields, filters, embeds and sorts with `requires()` get `x-lunar-requires` on their schema instead of being omitted.

Descriptions are developer-facing in the same way docblocks are: English only, matching the rest of Lunar's documentation, ASCII only per the house rule, and outside the 16-locale convention. The document is a build artefact for developers, not text a shopper or staff member sees.

### C. Request bodies

`Lunar\Api\OpenApi\RulesToSchema` converts a form request's `rules()` into an object schema. The form request is found by reflecting the route action's parameters for a `FormRequest` subclass. Supported rules and their mapping:

| Rule | Schema |
| --- | --- |
| `required` | listed in `required` |
| `nullable` | type array includes `null` |
| `string`, `email`, `url`, `uuid`, `ulid`, `date`, `date_format` | `string` with matching `format` where JSON Schema has one |
| `integer`, `numeric` | `integer`, `number` |
| `boolean`, `accepted` | `boolean` |
| `array`, `list` | `array`; `field.*` rules describe `items`; `field.key` rules describe object properties |
| `in:a,b` | `enum` |
| `min`, `max`, `between`, `size`, `digits` | `minimum`/`maximum` for numbers, `minLength`/`maxLength` for strings, `minItems`/`maxItems` for arrays |
| `Rule::enum()`, `Rule::in()` objects | `enum` |
| anything else | ignored; the field keeps the type its other rules gave it, or `{}` |

Conditional rules (`sometimes`, `required_if`, closures, custom rule objects) are ignored for typing. The converter is a best-effort mapping, so `descriptions()` and, where needed, a `DescribesSchema` override on the request (`public static function schema(): Schema`) take precedence over inference.

### D. Operations, parameters and tags

The generator walks the router for routes whose name starts with `lunar.api.{surface}.{version}.`, the prefix the service provider already applies. For each route:

- **Path** is the route URI relative to the surface prefix, with `{id}` typed as string and described as the resource's public id.
- **operationId** is the route name minus the prefix, camel-cased: `products.index` becomes `productsIndex`, `carts.lines.store` becomes `cartsLinesStore`. Route names are already stable and unique per surface; extension routes register under the resource prefix and name, so they get ids in the same scheme.
- **Tag** is the first segment of the relative route name, which is the resource type the route was registered under. Extension routes therefore file under their resource without declaring anything. The `openapi.json` route itself is `x-hidden`.
- **Query parameters** for index and any route whose `Responds` is `many`:
  - `include`: string. Comma-separated, so an `enum` would be wrong; the description lists every include path to `pagination.max_include_depth` and `x-lunar-includes` carries them as an array.
  - `fields`: `deepObject` parameter whose properties are the resource types reachable from this one, each a comma-separated string described with the type's field names.
  - `filter`: `deepObject` parameter, one property per filter. Each property is `oneOf` the filter's value type (the `eq` shorthand) and an object whose properties are the allowed operators, each with the value type (`in` and `not_in` accept an array or a comma-separated string).
  - `sort`: string, described with the sortable names and the `-` prefix; `x-lunar-sorts` carries the names.
  - `page[number]`, `page[size]` (integer, maximum from the resource) and, when `supportsCursorPagination()`, `page[cursor]`.
  - Show routes get `include` and `fields` only.
- **Headers** are component parameters referenced per surface. Storefront: `X-Lunar-Channel`, `X-Lunar-Currency`, `X-Lunar-Cart`, `Accept-Language`, all optional, described from the middleware behaviour; the response headers `X-Lunar-Cart`, `Content-Language` and the echoed context headers are declared on responses. Admin: none beyond authorization.
- **Security**: admin declares an `http` bearer scheme named `apiKey` applied to every operation. Storefront operations are unauthenticated, stated with an empty root `security` list so linters know it is deliberate, except those behind `ResolveCustomer`, which reference a `customer` bearer scheme; the scheme is emitted only when `lunar.api.storefront.guard` is set.
- **Responses**: the declared success response; 401 on authenticated routes; 403 when the route requires an ability; 404 on routes with `{id}`; 422 when the route has a request body or accepts query parameters; 429 on every route. All errors reference the shared `ErrorResponse` component.

Tags are emitted at document root in registration order, built-ins first, each with `name` (the type), `x-group` (the label, which Mintlify renders as the group name while keeping the type as the URL) and `description`.

### E. Components

- One schema per registered resource, named by the singular studly form of `type()` (`products` is `Product`, `collection-groups` is `CollectionGroup`, `api-keys` is `ApiKey`), with every field and embed as a property. The label stays a display name for tags and summaries. Fields are `required` unless nullable. Embeds are optional and described as "present when included". A `replace()`d resource contributes its own schema under the same name.
- `Money` (`amount` integer, `currency` string, `decimal_places` integer, `formatted` string), `TranslationMap` (map of string), `ErrorResponse` (`errors` array of `Error`: `status`, `code`, `title`, optional `detail`, optional `source` with `parameter`, `pointer` or `header`), `PaginationMeta` (page and cursor variants), `Links`.
- Item and collection envelopes are inlined per response as `{ data: $ref, meta?, links? }` rather than one component per resource per shape, which keeps `components.schemas` to the data models Mintlify turns into pages. Shared schemas and error responses nothing references are pruned (the storefront never emits `TranslationMap`); resource schemas always stay.

### F. Output

- `GET /{surface-prefix}/{version}/openapi.json` replaces `_schema`. It emits the full surface regardless of the caller's abilities, because codegen needs every field. Ability gates are visible through `x-lunar-requires`. The route sits inside the surface middleware group so it is throttled and, on the admin surface, authenticated like every other admin route.
- `lunar:api:openapi {surface} {--api-version=v1} {--out=} {--yaml}` replaces `lunar:api:schema`. Without `--out` the document prints to stdout.
- `info.title` is "{Surface label} API", `info.version` is the wire version (`v1`, per 0077 the wire contract, not the Lunar release) and `info.x-lunar-release` is the installed package version.
- `servers` comes from `lunar.api.{surface}.servers`, a list of `{url, description}` entries, defaulting to a single entry built from `app.url` and the surface prefix. Mintlify's playground needs at least one server and shows a selector for several.
- The generator memoises the finished document per `SurfaceRegistry` instance. Resources are registered at boot, so nothing invalidates it at runtime; a long-lived worker rebuilds it once per process, which is the same lifetime the registry has.

### G. Extension points

- Add-on `ResourceExtension` fields, filters, embeds, sorts and routes appear in the document with no extra step. Their obligations are `->type()` on closure fields and filters, `->describe()` where the name is not self-explanatory, and `Operation`/`Responds` attributes on their controller methods. Missing types degrade to `x-lunar-untyped`; missing attributes fall back to the method-and-path title Mintlify generates.
- `SurfaceRegistry::tapDocument(Closure $tap)` registers a closure receiving the finished `Lunar\Api\OpenApi\Document` before serialisation. This is the escape hatch for anything the declarative model does not cover: a host adding a second `servers` entry, an add-on attaching a `callbacks` block, a store adding `x-mint` content. The `Document` exposes `paths()`, `components()`, `tags()`, `get()`, `set()` and `forget()` over the array; paths are dotted strings, or arrays of segments when a key contains a dot (`['paths', '/products/{id}', 'get']`).
- `x-lunar-*` is the reserved extension namespace. Other vendors' extensions (`x-mint`, `x-group`, `x-hidden`) are emitted only where this spec says.

### H. Consumption

**Orval and friends.** `orval --input https://store.test/api/storefront/v1/openapi.json` or a committed file. Tags group the generated client, `operationId` names the functions, `description` becomes JSDoc. The `deepObject` filter parameter generates a typed filter object.

**Mintlify.** The Lunar docs repo lists both documents in `docs.json` under two tabs, "Storefront API" and "Admin API". Auto-generated pages group by tag and take their title from `summary`. `components.schemas` back the data model pages through `openapi-schema` frontmatter. Prose beyond the in-code descriptions goes in the docs repo through overlays or MDX pages with `openapi:` frontmatter, so a wording change does not need a package release.

**Publishing.** An `openapi` CI job boots the packages through `testbench.yaml` (no host app, no database), runs `lunar:api:openapi` for both surfaces, lints each document with `npx @redocly/cli lint` (a CI-only tool, not a Composer or workspace dependency), and uploads the two documents as build artefacts; a release workflow step pushes them to the docs repo. A store running add-ons generates its own documents from its own install, which is where the derived tags and descriptions earn their place.

## Alternatives considered

- **A code scanner (Scramble, l5-swagger with swagger-php attributes).** They read controllers and `JsonResource` classes statically. Closure-resolved fields, runtime registration and add-on extensions are invisible to them, so the document would describe the controllers and none of the resources. Also a runtime dependency. Rejected.
- **A hand-written `openapi.yaml` in the repo.** Goes stale against the registry, cannot know about add-ons, and forks the source of truth the registry was built to be. Rejected.
- **Keep `_schema` and add OpenAPI beside it.** Two introspection formats to keep in step for no consumer that needs both. OpenAPI carries everything `_schema` did (abilities via `x-lunar-requires`, operators, pagination) and is unreleased, so replacing it costs nothing. Rejected.
- **Generate a TypeScript client from the package.** Orval, openapi-typescript and Kiota already do this well from OpenAPI and let each frontend choose its HTTP client. Shipping our own would mean maintaining a generator for one language. Rejected.
- **Types inferred from executing resolvers against factory models.** Accurate but requires a database at generation time and fixtures per resource, and cannot see nullability. Rejected in favour of declarations with cast inference.
- **OpenAPI 3.0.x.** Nullability is `nullable: true` rather than a type array, and `deepObject` parameters are the same. 3.1 aligns with JSON Schema 2020-12, which is what Orval and Mintlify parse today. An `--openapi-version=3.0` flag can be added if a consumer's toolchain needs it; not in scope.

## Migration impact

- **Database migrations**: none.
- **Public contract**: `_schema`, `SchemaController`, `SchemaCommand` and `ResourceDefinition::schema()` are removed. All are unreleased and live only in the open 0077 pull request, so this is a change to the draft, not a break. Additions (`Schema`, `type()`, `describe()`, `label()`, `description()`, `many()`, the attributes, `tapDocument()`, `DescribesSchema`) are additive.
- **Upgrade path**: none; v1.x has no API package.
- **Translations**: none. Descriptions are developer-facing and English only (see section B). The command and error strings it adds (`api::errors.*` for an unknown surface or version) are translated into all 16 locales as usual.
- **Filament / panel**: none.

## Open questions

- **Filter parameter shape for clients.** `deepObject` gives Orval a typed filter object but some generators flatten `filter[price][gte]` badly. If that bites, an alternative is one flat parameter per filter and operator, at the cost of very long parameter lists. Decide after generating against Orval in slice 4.
- **Storefront customer security scheme.** The customer guard is host-configured, so the document can only assume bearer. If a host uses a cookie session guard the scheme is wrong. A `lunar.api.storefront.security` config entry (`bearer`, `cookie`, `none`) is the likely answer; decide in [[0078-storefront-api]] when the customer area lands.

## References

- [[0077-api-platform]] — the registry, `Field`, `Embed`, `Filter`, `Sort`, `_schema` this spec replaces, and the "OpenAPI and a TypeScript client" follow-on it names.
- [[0078-storefront-api]], [[0079-admin-api]] — every write endpoint they add is typed through this spec's `Responds` and request-body mapping.
- OpenAPI 3.1.0 specification; OpenAPI Overlay 1.0 specification (used by Mintlify's `overlays`).
- Mintlify OpenAPI setup: `https://www.mintlify.com/docs/api-playground/openapi-setup` (`summary` as title, tag grouping, `x-group`, `x-hidden`, `x-mint`, `openapi-schema` pages, `servers` for the playground).
- Orval: `https://orval.dev/`.

## Implementation plan

- [x] Slice 1 — `Schema` value class; `type()` on `Field` and `Filter`; `many()` on `Embed`; cast inference; relation cardinality; `x-lunar-untyped` fallback; every built-in field and filter typed; `OpenApiCoverageTest`.
- [x] Slice 2 — `label()` and `description()` on `Resource`; `describe()` on `Field`, `Filter`, `Embed`, `Sort`; `Operation` and `Responds` attributes; `DescribesSchema`; `descriptions()` on form requests; `ResourceController` conventions; every built-in endpoint, field and filter described.
- [x] Slice 3 — `RulesToSchema`; the generator (paths, parameters, headers, security, responses, components, tags, servers, info); `Document` and `tapDocument()`; `openapi.json` route and `lunar:api:openapi`; remove `_schema`, `SchemaController`, `SchemaCommand` and `ResourceDefinition::schema()`; update `README.md` and the 0077 spec; feature tests for both surfaces and for an extension's fields and routes appearing in the document.
- [x] Slice 4 — CI: generate both documents in the `api` job, lint them with `@redocly/cli`, upload as artefacts; structural tests in the `api` suite (every operation has a summary, every `$ref` resolves); Orval smoke run against the storefront document recorded in the PR; docs repo wiring (`docs.json` tabs, overlays) tracked in the docs repo.
