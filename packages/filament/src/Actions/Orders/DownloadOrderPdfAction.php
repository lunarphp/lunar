<?php

namespace Lunar\Filament\Actions\Orders;

use Lunar\Core\Models\Order;
use Lunar\Filament\Actions\Support\DownloadPdfAction;

class DownloadOrderPdfAction extends DownloadPdfAction
{
    public static function getDefaultName(): ?string
    {
        return 'download_pdf';
    }

    protected function setUp(): void
    {
        $this->pdfView('lunarpanel::pdf.order');

        $this->filename(fn (Order $record) => "Order-{$record->reference}.pdf");

        parent::setUp();

        $this->label(__('lunar-filament::actions.orders.download_pdf.label'));
    }
}
