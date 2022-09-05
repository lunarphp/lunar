<?php

namespace GetCandy\Hub\Http\Controllers;

use GetCandy\Hub\GetCandyHub;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StylesController extends Controller
{
    /**
     * @param  string  $style
     * @return \GetCandy\Hub\Assets\Script
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $style)
    {
        $asset = collect(GetCandyHub::styles())
            ->filter(function ($asset) use ($style) {
                return $asset->name() === $style;
            })->first();

        if (! $asset) {
            abort(404);
        }

        return $asset;
    }
}
