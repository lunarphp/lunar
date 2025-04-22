<?php

namespace Lunar\Base;

use Lunar\Facades\DB;
use Lunar\Models\Order;

class OrderReferenceGenerator implements OrderReferenceGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(Order $order): string
    {
        $year = $order->created_at->year;

        $month = $order->created_at->format('m');

        $connection = DB::connection()->getDriverName();

        $rawSelect = $connection != 'sqlite' ?
            'MAX(CAST(SUBSTRING(reference from 9) as UNSIGNED))' :
            'MAX(CAST(substr(reference, 9) as UNSIGNED))';

        $latest = Order::select(
            DB::RAW($rawSelect.' as reference')
        )->whereYear('placed_at', '=', $year)
            ->where('reference', 'LIKE', $year.'-'.$month.'-%')
            ->where('id', '!=', $order->id)->first();

        return $year.'-'.$month.'-'.str_pad($latest?->reference + 1, 4, 0, STR_PAD_LEFT);
    }
}
