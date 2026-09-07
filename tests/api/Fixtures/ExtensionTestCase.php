<?php

namespace Lunar\Tests\Api\Fixtures;

use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Storefront\Resources\V1\ProductResource;
use Lunar\Tests\Api\TestCase;

/**
 * Registers the reviews extension the way an add-on's service provider would:
 * before the app boots, so its routes are registered with the surface.
 */
class ExtensionTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app->afterResolving(ApiManager::class, function (ApiManager $api): void {
            $api->storefront('v1')->extend(ProductResource::class, ReviewsProductExtension::class);
        });
    }
}
