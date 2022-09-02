<?php

namespace GetCandy\Hub\Views\Components\GetCandy;

use Composer\InstalledVersions;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Version extends Component
{
    public $installedVersion;

    public function __construct()
    {
        $installedVersion = InstalledVersions::getPrettyVersion('getcandy/getcandy');

        $prettyVersion = Str::contains($installedVersion, [
            'dev',
            'feat',
            'fix',
            'hotfix',
            'update',
        ]) ? '2.0-beta' : $installedVersion;

        $this->installedVersion = $prettyVersion;
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
