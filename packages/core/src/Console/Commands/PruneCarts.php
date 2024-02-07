<?php

namespace Lunar\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Lunar\Models\Cart;

class PruneCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:prune:carts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune the carts table';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Beginning prune');

        $query = Cart::query();

        $carts = app(Pipeline::class)
            ->send($query)
            ->through(
                config('lunar.cart.prune_tables.pipelines', [])
            )->then(function ($query) {
                $query->chunk(200, function ($carts) {
                    $carts->each(fn ($cart) => $this->pruneCart($cart));
                });
            });

        $this->info('Prune complete');
    }

    public function pruneCart(Cart $cart)
    {
        Cart::where('merged_id', $cart->id)->get()->each(fn ($merged) => $this->pruneCart($merged));

        $cart->lines()->delete();
        $cart->addresses()->delete();
        $cart->delete();
    }
}
