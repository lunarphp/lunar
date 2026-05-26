<?php

namespace Lunar\Core\Contracts;

interface TelemetryService
{
    public function optOut(): void;

    public function getInsightsUrl(): string;

    public function getCacheKey(): string;

    public function shouldRun(): bool;

    public function run(): void;
}
