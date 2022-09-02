<?php

namespace GetCandy\Hub\Views\Components\GetCandy;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Version extends Component
{
    public $installedVersion;

    public function __construct()
    {
        try {
            $packageManifest = json_decode(File::get(base_path('vendor/composer/installed.json')));

            $installedPackages = is_array($packageManifest)
                ? collect($packageManifest)
                : collect($packageManifest->packages);
        } catch (FileNotFoundException $e) {
            $this->installedVersion = config('getcandy-hub.system.version_fallback');

            return;
        }

        $candyVersion = $installedPackages->first(function ($installedPackage) {
            return $installedPackage->name === 'getcandy/getcandy';
        })->version;

        $semverKeys = [
            'dev',
        ];

        $isSemver = Str::contains($candyVersion, $semverKeys);

        $this->installedVersion = ! $isSemver
            ? $candyVersion
            : config('getcandy-hub.system.version_fallback');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.getcandy.version');
    }
}
