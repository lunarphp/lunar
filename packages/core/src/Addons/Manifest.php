<?php

namespace GetCandy\Addons;

use GetCandy\Licensing\LicenseManager;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * This manifest was heavily inspired by Statamic..
 */
class Manifest extends PackageManifest
{
    /**
     * Build the addon manifest.
     *
     * @return void
     */
    public function build()
    {
        $this->manifest = null;

        $packages = [];

        if ($this->files->exists($path = $this->vendorPath.'/composer/installed.json')) {
            $installed = json_decode($this->files->get($path), true);
            $packages = $installed['packages'] ?? $installed;
        }

        $this->write(collect($packages)->filter(function ($package) {
            return Arr::has($package, 'extra.getcandy');
        })->keyBy('name')->map(function ($package) {
            return $this->formatPackage($package);
        })->filter()->all());

        $this->getManifest();
    }

    /**
     * Format a given composer package into our addon format.
     *
     * @param array $package
     *
     * @return array
     */
    protected function formatPackage($package)
    {
        if (!$provider = $package['extra']['laravel']['providers'][0] ?? null) {
            return;
        }

        $reflector = new ReflectionClass($provider);
        $providerParts = explode('\\', $provider, -1);
        $namespace = implode('\\', $providerParts);
        $autoload = $package['autoload']['psr-4'][$namespace.'\\'];

        $directory = Str::remove($autoload, dirname($reflector->getFileName()));
        $json = json_decode(File::get($directory.'composer.json'), true);

        $getcandy = $json['extra']['getcandy'] ?? [];
        $author = $json['authors'][0] ?? null;

        $config = config('getcandy.addons.'.$package['name'], [
            'license' => null,
        ]);

        $license = LicenseManager::fetch($package['name'], $config);

        return [
            'id'             => $package['name'],
            'slug'           => $getcandy['slug'] ?? null,
            'editions'       => $getcandy['editions'] ?? [],
            'marketplaceId'  => data_get($license, 'id', null),
            'marketplaceUrl' => data_get($license, 'url', null),
            'licensed'       => data_get($license, 'licensed', false),
            'latestVersion'  => data_get($license, 'latestVersion', null),
            'version'        => $package['version'],
            'namespace'      => $namespace,
            'autoload'       => $autoload,
            'provider'       => $provider,
            'name'           => $statamic['name'] ?? Arr::last($providerParts),
            'author'         => $author['name'] ?? null,
            'email'          => $package['support']['email'] ?? null,
        ];
    }

    /**
     * Get a collection of our addons.
     *
     * @return \Illuminate\Support\Collection
     */
    public function addons(): Collection
    {
        return collect($this->getManifest());
    }
}
