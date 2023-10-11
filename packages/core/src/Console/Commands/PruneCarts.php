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
            )->then(fn ($query) => $query->get());
            
        $carts->each(function ($cart) {
            Cart::where('merged_id', $cart->id)->update(['merged_id' => null]);
            
            $cart->lines()->delete();
            $cart->addresses()->delete();
            $cart->delete();
        });
        
        $this->info('Prune complete');
    }
}
