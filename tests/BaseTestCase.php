<?php

namespace Lunar\Tests;

use Lunar\Facades\ModelManifest;
use Spatie\StructureDiscoverer\Discover;

class BaseTestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (env('LUNAR_TESTING_REPLACE_MODELS', false)) {
            $this->replaceModels();
        }
    }

    /**
     * Replaces the models in the ModelManifest with the models in the stubs directory.
     */
    protected function replaceModels(): void
    {
        $modelClasses = Discover::in(__DIR__.'/stubs/Models')
            ->classes()
            ->get();

        foreach ($modelClasses as $modelClass) {
            $interfaceClass = ModelManifest::guessContractClass($modelClass);
            ModelManifest::replace($interfaceClass, $modelClass);
        }

        ModelManifest::morphMap();
    }
}
