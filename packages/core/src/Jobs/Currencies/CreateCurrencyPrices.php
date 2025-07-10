<?php

namespace Lunar\Jobs\Currencies;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lunar\Models\Contracts\Currency;

class CreateCurrencyPrices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tries = 1;

    public function __construct(public Currency $currency) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $default = \Lunar\Models\Currency::where('default', true)->first();

        // Check whether this new currency has been made default
        // if that is the case we will need to find which
        // currency has just been made non default.
        if ($default->id == $this->currency->id) {
            $default = \Lunar\Models\Currency::whereBetween(
                'updated_at',
                [now()->subSeconds(15), now()]
            )->whereDefault(false)->first();
        }

        if (! $default || $default->id == $this->currency->id) {
            return;
        }

        (new \Lunar\Actions\Currencies\CreateCurrencyPrices)->handle($this->currency, $default);
    }
}
