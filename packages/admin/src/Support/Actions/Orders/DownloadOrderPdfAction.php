<?php

namespace Lunar\Admin\Support\Actions\Orders;

use Lunar\Admin\Support\Actions\DownloadPdfAction;
use Lunar\Core\Models\Order;

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

        $this->label(__('lunarpanel::order.action.download_order_pdf.label'));
    }
}
