<?php

namespace Lunar\Tests\Core;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Lunar\Tests\LunarTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends LunarTestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
        ]);
    }
}
