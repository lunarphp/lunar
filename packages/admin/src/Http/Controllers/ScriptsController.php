<?php

namespace GetCandy\Hub\Http\Controllers;

use GetCandy\Hub\GetCandyHub;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScriptsController extends Controller
{
    /**
     * @param  string  $script
     * @return \GetCandy\Hub\Assets\Script
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
