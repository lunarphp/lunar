<?php

namespace Lunar\Admin\Http\Controllers;

use App\Data\SiteMessageData;
use App\Models\SiteMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class DownloadPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        $request->validate([
            'record' => 'required',
            'record_type' => 'required',
            'view' => 'required',
        ]);

        $recordType = $request->get('record_type');
        $view = $request->get('view');
        $record = $request->get('record');

        $model = $recordType::find($record);

        return Pdf::loadView($view, [
            'record' => $model,
        ])->stream();
    }
}