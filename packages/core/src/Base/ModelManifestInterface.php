<?php

namespace Lunar\Base;

interface ModelManifestInterface
{
    public function register(): void;

    public function addDirectory(string $dir): void;

    public function add(string $interfaceClass, string $modelClass): void;

    public function replace(string $interfaceClass, string $modelClass): void;

    public function get(string $interfaceClass): ?string;

    public function guessContractClass(string $modelClass): string;
}
