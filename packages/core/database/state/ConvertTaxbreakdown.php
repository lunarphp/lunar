<?php

namespace Lunar\Database\State;

use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\DataTypes\Price;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;

class ConvertTaxbreakdown
{
    public function prepare()
    {
        //
    }

    public function run()
    {
        Order::chunk(500, function ($orders) {
           foreach ($orders as $order) {
               if (is_a($order->tax_breakdown, TaxBreakdown::class)) {
                   continue;
               }
               // Get the raw tax_breakdown
               $breakdown = json_decode($order->getRawOriginal('tax_breakdown'), true);


               $amounts = collect($breakdown)->map(function ($row) use ($order) {
                  return new TaxBreakdownAmount(
                      price: new Price($row['total'], $order->currency),
                      identifier: $row['identifier'] ?? $row['description'],
                      description: $row['description'],
                      percentage: $row['percentage'],
                  );
               });

               $order->updateQuietly([
                   'tax_breakdown' => new \Lunar\Base\ValueObjects\Cart\TaxBreakdown($amounts),
               ]);
           }
        });

        OrderLine::chunk(500, function ($orderLines) {
            foreach ($orderLines as $orderLine) {
                // Get the raw tax_breakdown
                $breakdown = json_decode($orderLine->getRawOriginal('tax_breakdown'), true);
                
                $amounts = collect($breakdown)->map(function ($row) use ($orderLine) {
                    return new TaxBreakdownAmount(
                        price: new Price($row['total'], $orderLine->order->currency),
                        identifier: $row['identifier'] ?? $row['description'],
                        description: $row['description'],
                        percentage: $row['percentage'],
                    );
                });

                $orderLine->updateQuietly([
                    'tax_breakdown' => new \Lunar\Base\ValueObjects\Cart\TaxBreakdown($amounts),
                ]);
            }
        });
    }

    protected function canRun()
    {
        return true;
    }
}
