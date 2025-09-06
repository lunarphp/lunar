<?php

namespace Lunar\Api\Resources;

use ApiPlatform\Laravel\Eloquent\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IsApiResource;
use ApiPlatform\Metadata\QueryParameter;
use Illuminate\Database\Eloquent\Model;
use Lunar\Api\State\ModelManifestCollectionProvider;
use Lunar\Api\State\ModelManifestItemProvider;

final class Brand
{
    use IsApiResource;

    public int $id;

    public string $name;

    public static $manifestMorph = \Lunar\Models\Contracts\Brand::class;

    public function __construct(Model $model)
    {
        $this->id = $model->id;

        $this->name = $model->name;
    }

    public static function apiResource(): ApiResource
    {
        return new ApiResource(
            operations: [
                new Get(
                    output: self::class,
                    provider: ModelManifestItemProvider::class,
                    uriTemplate: '/brands/{id}'
                ),
                new GetCollection(
                    output: self::class,
                    provider: ModelManifestCollectionProvider::class,
                    uriTemplate: '/brands',
                    parameters: [
                        'sort[:property]' => new QueryParameter(
                            filter: new OrderFilter,
                            properties: ['name'],
                            property: 'name'
                        ),
                    ]
                ),
            ],
        );
    }
}
