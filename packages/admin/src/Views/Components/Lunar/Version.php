<?php

namespace Lunar\Hub\Views\Components\Lunar;

use Composer\InstalledVersions;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Version extends Component
{
    public $installedVersion;

    public function __construct()
    {
        $installedVersion =
            InstalledVersions::getPrettyVersion('lunarphp/core')
                ?? InstalledVersions::getPrettyVersion('lunarphp/lunar');

        $prettyVersion = Str::contains($installedVersion, [
            'dev',
            'feat',
            'fix',
            'hotfix',
            'update',
        ]) ? '' : $installedVersion;

        $this->installedVersion = $prettyVersion;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.lunar.version');
    }
}
