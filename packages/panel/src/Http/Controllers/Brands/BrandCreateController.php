<?php

namespace Lunar\Panel\Http\Controllers\Brands;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Brands\CreatesBrand;
use Lunar\Panel\Http\Requests\Brands\BrandRequest;

class BrandCreateController
{
    public function create(): Response
    {
        return Inertia::render('brands/Create', [
            'urls' => [
                'store' => route('panel.brands.store'),
                'index' => route('panel.brands.index'),
            ],
        ]);
    }

    public function store(BrandRequest $request, CreatesBrand $createsBrand): RedirectResponse
    {
        $brand = $createsBrand->execute($request->brandAttributes());

        return redirect()
            ->route('panel.brands.edit', $brand)
            ->with('success', __('panel::brands.flash_created'));
    }
}
