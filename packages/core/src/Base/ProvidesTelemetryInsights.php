<?php

namespace Lunar\Base;

use Illuminate\Support\Collection;

interface ProvidesTelemetryInsights
{
    public function domainHash(): string;

    public function environment(): string;

    public function laravelVersion(): string;

    public function lunarVersion(): string;

    public function dbDriver(): string;

    public function phpVersion(): string;

    public function productCount(): int;

    public function productVariantCount(): int;

    public function orderCount(): int;

    public function orderTotal(): int;

    /**
     * @return Collection<string>
     */
    public function currencies(): Collection;

    /**
     * @return Collection<string>
     */
    public function languages(): Collection;
}
