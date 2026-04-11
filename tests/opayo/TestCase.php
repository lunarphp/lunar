<?php

namespace Lunar\Tests\Opayo;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Opayo\OpayoServiceProvider;
use Lunar\Shipping\ShippingServiceProvider;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $lastTransactionFixture = null;

        $getResponse = fn ($file) => Http::response(
            json_decode(
                file_get_contents(
                    __DIR__."/Opayo/{$file}.json"
                ), true
            )
        );

        Http::fake([
            'https://sandbox.opayo.eu.elavon.com/api/v1/transactions' => function (Request $request) use (&$lastTransactionFixture, $getResponse) {
                $lastTransactionFixture = match ($request->data()['paymentMethod']['card']['merchantSessionKey']) {
                    'SUCCESS' => 'transaction_201',
                    'FAILED' => 'transaction_not_authed',
                    'SUCCESS_3DSV2' => 'transaction_202',
                    default => null,
                };

                return $lastTransactionFixture
                    ? $getResponse($lastTransactionFixture)
                    : Http::response('ok');
            },
            'https://sandbox.opayo.eu.elavon.com/api/v1/transactions/DB79BA2D-05DA-5B85-D188-1293D16BBAC7' => fn () => $lastTransactionFixture
                ? $getResponse($lastTransactionFixture)
                : Http::response('ok'),
            'https://sandbox.opayo.eu.elavon.com/api/v1/transactions/3DSV2_SUCCESS/3d-secure-challenge' => fn (Request $request) => $getResponse('3dsv2_successful'),
            'https://sandbox.opayo.eu.elavon.com/api/v1/transactions/3DSV2_FAILURE/3d-secure-challenge' => fn (Request $request) => $getResponse('3dsv2_not_authed'),
            'https://sandbox.opayo.eu.elavon.com/api/v1/transactions/3DSV2_SUCCESS' => fn (Request $request) => $getResponse('3dsv2_successful'),
            'https://sandbox.opayo.eu.elavon.com/api/v1/transactions/3DSV2_FAILURE' => fn (Request $request) => $getResponse('3dsv2_not_authed'),
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
            ShippingServiceProvider::class,
            BlinkServiceProvider::class,
            OpayoServiceProvider::class,

        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->replaceModelsForTesting();
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
