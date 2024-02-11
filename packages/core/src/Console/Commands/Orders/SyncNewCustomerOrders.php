<?php

namespace Lunar\Console\Commands\Orders;

use Illuminate\Console\Command;
use Lunar\Jobs\Orders\MarkAsNewCustomer;
use Lunar\Models\Order;

class SyncNewCustomerOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:orders:sync-new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates historic orders to whether they were a new customer or not.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Order::orderBy('id')->chunk(500, function ($orders) {
            foreach ($orders as $order) {
                MarkAsNewCustomer::dispatch($order->id);
            }
        });

        return Command::SUCCESS;
    }
}
