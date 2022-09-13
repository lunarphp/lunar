<?php

namespace Lunar\Hub\Http\Controllers;

use Illuminate\Routing\Controller;
use Lunar\Hub\GetCandyHub;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScriptsController extends Controller
{
    /**
     * @param  string  $script
     * @return \Lunar\Hub\Assets\Script
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $script)
    {
        $asset = collect(GetCandyHub::scripts())
            ->filter(function ($asset) use ($script) {
                return $asset->name() === $script;
            })->first();

        if (! $asset) {
            abort(404);
        }

        return $asset;
    }
}
