<?php

namespace Lunar\Core\Contracts;

interface ModelManifest
{
    public function register(): void;

    public function addDirectory(string $dir): void;

    public function morphMap(): void;

    public function getMorphMapKey(string $className): string;
}
