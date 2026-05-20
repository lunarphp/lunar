<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Support;

use Composer\InstalledVersions;
use Lunar\Upgrade\Exceptions\UpgradeAbortedException;

class VersionGuard
{
    /**
     * The earliest v1.x release the upgrade tool supports running against.
     *
     * Tightened to the final v1.x release as later specs land.
     */
    public const MINIMUM_V1_VERSION = '1.0.0';

    public const PACKAGE = 'lunarphp/core';

    public function assertLatestV1(): void
    {
        $version = $this->installedVersion();

        if ($version === null) {
            throw new UpgradeAbortedException(
                'Lunar v1.x is not installed in this application.',
                'Install lunarphp/core ^1 before running `php artisan lunar:upgrade`.',
            );
        }

        $major = (int) explode('.', ltrim($version, 'v'))[0];

        if ($major !== 1) {
            throw new UpgradeAbortedException(
                "Detected lunarphp/core {$version}; the upgrade tool only runs against v1.x.",
                'Run `composer require lunarphp/core:^1` to pin to the latest v1.x before upgrading.',
            );
        }

        if (version_compare(ltrim($version, 'v'), self::MINIMUM_V1_VERSION, '<')) {
            throw new UpgradeAbortedException(
                "lunarphp/core {$version} is older than the supported minimum of ".self::MINIMUM_V1_VERSION.'.',
                'Run `composer update lunarphp/core` to the latest v1.x before upgrading.',
            );
        }
    }

    public function installedVersion(): ?string
    {
        if (! class_exists(InstalledVersions::class)) {
            return null;
        }

        if (! InstalledVersions::isInstalled(self::PACKAGE)) {
            return null;
        }

        return InstalledVersions::getPrettyVersion(self::PACKAGE);
    }
}
