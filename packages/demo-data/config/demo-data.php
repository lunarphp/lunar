<?php

return [

    /*
     | The faker seed. Fixed so the same scale always produces the same store —
     | stable screenshots and review diffs. Override per-environment if you want
     | a different (but still reproducible) dataset.
     */
    'faker_seed' => env('LUNAR_DEMO_FAKER_SEED', 4242),

    /*
     | Scale used when `lunar:demo-data` is run without `--scale`.
     */
    'default_scale' => 'small',

    /*
     | The filesystem disk placeholder product media is copied onto. Must be
     | writable; mirrors `lunar.media.disk` by default.
     */
    'asset_disk' => env('LUNAR_DEMO_ASSET_DISK', 'public'),

    /*
     | Currencies the store is seeded with. The first is treated as the default.
     */
    'currencies' => ['GBP', 'USD', 'EUR'],

    /*
     | Per-scale volume knobs. Every scale guarantees full order-status and
     | fulfilment-method coverage; larger scales layer a natural distribution on
     | top (see the demo-data spec, sections C.4 and D).
     */
    'scales' => [
        'small' => [
            'products' => 12,
            'collections' => 3,
            'customers' => 8,
            'orders' => 10,
        ],
        'medium' => [
            'products' => 50,
            'collections' => 6,
            'customers' => 40,
            'orders' => 50,
        ],
        'large' => [
            'products' => 250,
            'collections' => 12,
            'customers' => 200,
            'orders' => 250,
        ],
    ],

];
