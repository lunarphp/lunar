<?php

namespace Lunar\Tests;

use Lunar\Facades\ModelManifest;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\StructureDiscoverer\Discover;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->replaceModelsForTesting();
    }

    /**
     * Replace Lunar models with test models for testing
     * functionality with model extending.
     */
    protected function replaceModelsForTesting(): void
    {
        if (! env('LUNAR_TESTING_REPLACE_MODELS', false)) {
            return;
        }

        $modelClasses = Discover::in(__DIR__.'/core/Stubs/Models')
            ->classes()
            ->get();

        foreach ($modelClasses as $modelClass) {
            $interfaceClass = ModelManifest::guessContractClass($modelClass);
            ModelManifest::replace($interfaceClass, $modelClass);
        }
    }
}
