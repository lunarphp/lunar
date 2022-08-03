<?php

namespace GetCandy\Hub\Http\Livewire\Components\Orders;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Spatie\Activitylog\Contracts\Activity;

class EmailNotification extends Component
{
    public $showPreview = false;

    public Activity $log;

    public function getPreviewHtmlProperty()
    {
        return Storage::get(
            $this->log->getExtraProperty('template')
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getName()
    {
        return 'hub.order-notification.email-notification';
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('adminhub::livewire.components.orders.email-notification-log');
    }
}
