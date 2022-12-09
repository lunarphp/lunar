<?php

namespace Lunar\Jobs\Orders;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

class MarkAsNewCustomer implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tries = 1;

    /**
     * The product instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected Order $order;

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Support\Collection  $tags
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            $billingAddress = $this->order->billingAddress;

            if (!$billingAddress) {
                return;
            }

            $ordersTable = (new Order)->getTable();

            $hasPreviousOrder = OrderAddress::where('order_id', '!=', $this->order->id)
                ->whereType('billing')
                ->whereContactEmail($billingAddress->contact_email)
                ->join(
                    $ordersTable,
                    "{$ordersTable}.id",
                    '=',
                    'order_id'
                )->whereDate('placed_at', '<', $this->order->placed_at)->exists();

            $this->order->new_customer = !$hasPreviousOrder;
            $this->order->saveQuietly();
        });
    }
}
