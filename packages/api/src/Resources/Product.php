<?php

namespace Lunar\Api\Resources;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IsApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Api\State\ModelManifestCollectionProvider;
use Lunar\Api\State\ModelManifestItemProvider;

final class Product
{
    use IsApiResource;

    public int $id;

    public Brand $brand;

    public Collection $productType;

    public array $attributeData;

    public string $status;

    public static $manifestMorph = \Lunar\Models\Contracts\Product::class;

    public function __construct(Model $model)
    {
        $this->id = $model->id;

        // @TODO: should be a DTO
        //        $this->brand = collect([
        //            'id' => $model->brand_id,
        //            'name' => $model->brand->name,
        //        ]);
        if ($model->brand) {
            $this->brand = new Brand($model->brand);
        }

        // @TODO: should be a DTO
        $this->productType = collect([
            'id' => $model->product_type_id,
            'name' => $model->productType->name,
        ]);

        // @TODO: should be a DTO
        $this->attributeData = json_decode($model->attribute_data->toJson(), true);

        $this->status = $model->status;
    }

    public static function apiResource(): ApiResource
    {
        return new ApiResource(
            operations: [
                new Get(
                    output: self::class,
                    provider: ModelManifestItemProvider::class,
                    uriTemplate: '/products/{id}'
                ),
                new GetCollection(
                    output: self::class,
                    provider: ModelManifestCollectionProvider::class,
                    uriTemplate: '/products'
                ),
            ],
        );
    }
}
