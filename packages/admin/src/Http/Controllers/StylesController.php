<?php

namespace Lunar\Hub\Http\Controllers;

use Illuminate\Routing\Controller;
use Lunar\Hub\LunarHub;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StylesController extends Controller
{
    /**
     * @param  string  $style
     * @return \Lunar\Hub\Assets\Script
     *
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $style)
    {
        $asset = collect(LunarHub::styles())
            ->filter(function ($asset) use ($style) {
                return $asset->name() === $style;
            })->first();

        if (! $asset) {
            abort(404);
        }

        return $asset;
    }
}
