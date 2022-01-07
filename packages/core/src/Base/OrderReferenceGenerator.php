<?php

namespace GetCandy\Base;

use Closure;
use GetCandy\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderReferenceGenerator implements OrderReferenceGeneratorInterface
{
    /**
     * The override generator.
     *
     * @var \Closure
     */
    protected ?Closure $overrideCallback = null;

    /**
     * {@inheritDoc}
     */
    public function generate(Order $order): string
    {
        if ($this->overrideCallback) {
            return call_user_func($this->overrideCallback, $order);
        }

        $year = $order->created_at->year;

        $month = $order->created_at->format('m');

        $latest = Order::select(
            DB::RAW('MAX(reference) as reference')
        )->whereYear('created_at', '=', $year)
            ->whereMonth('created_at', '=', $month)
            ->where('id', '!=', $order->id)
            ->first();

        if (!$latest || !$latest->reference) {
            $increment = 1;
        } else {
            $segments = explode('-', $latest->reference);

            if (count($segments) == 1) {
                $increment = 1;
            } else {
                $increment = end($segments) + 1;
            }
        }

        return $year.'-'.$month.'-'.str_pad($increment, 4, 0, STR_PAD_LEFT);
    }

    /**
     * Override the current method of generating a reference.
     *
     * @param Closure $callback
     *
     * @return self
     */
    public function override(Closure $callback)
    {
        $this->overrideCallback = $callback;

        return $this;
    }
}
