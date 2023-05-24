<?php

namespace Lunar\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;

class TestUrlGenerator
{
    /**
     * The instance of the model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Handle the URL generation.
     *
     * @return void
     */
    public function handle(Model $model)
    {
        // ...
    }
}
