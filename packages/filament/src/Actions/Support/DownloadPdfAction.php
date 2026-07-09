<?php

namespace Lunar\Filament\Actions\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Generic "render a Blade view as a PDF" action. Subclass it (e.g. as
 * `Lunar\Filament\Actions\Orders\DownloadOrderPdfAction`) to pre-wire the
 * `pdfView` and filename for a specific resource.
 *
 * Honours `lunar.admin.pdf_rendering` — "stream" emits a temporary signed
 * URL handled by the existing PDF route; "download" streams the response
 * inline.
 */
class DownloadPdfAction extends Action
{
    protected string $pdfView = '';

    protected Closure|string|null $filename = null;

    public function pdfView(string $pdfView): self
    {
        $this->pdfView = $pdfView;

        return $this;
    }

    public function filename(Closure|string|null $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (config('lunar.admin.pdf_rendering', 'download') === 'stream') {
            $this->url(function ($record) {
                return URL::temporarySignedRoute(
                    'lunar.pdf.download',
                    now()->addMinutes(2),
                    [
                        'record' => $record->id,
                        'record_type' => $record->getMorphClass(),
                        'view' => $this->evaluate($this->pdfView),
                    ]
                );
            }, shouldOpenInNewTab: true);
        } else {
            $this->action(function ($record) {
                Notification::make()->title(
                    __('lunarpanel::order.action.download_order_pdf.notification')
                )->success()->send();

                return response()->streamDownload(function () use ($record) {
                    echo Pdf::loadView($this->evaluate($this->pdfView), [
                        'record' => $record,
                    ])->stream();
                }, name: $this->evaluate($this->filename));
            });
        }
    }
}
