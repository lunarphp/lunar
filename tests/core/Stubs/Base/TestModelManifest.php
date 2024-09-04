<?php

namespace Lunar\Tests\Core\Stubs\Base;

use Lunar\Base\ModelManifest;
use Spatie\StructureDiscoverer\Discover;

class TestModelManifest extends ModelManifest
{
    /**
     * Bind initial models in container and set explicit model binding.
     */
    public function register(): void
    {
        // Discover models
        $modelClasses = Discover::in(__DIR__.'/../Models')
            ->classes()
            ->get();

        foreach ($modelClasses as $modelClass) {
            $interfaceClass = $this->guessContractClass($modelClass);
            $this->models[$interfaceClass] = $modelClass;
            $this->bindModel($interfaceClass, $modelClass);
        }
    }
}
