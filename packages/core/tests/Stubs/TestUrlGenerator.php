<?php

namespace GetCandy\Tests\Stubs;

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
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function handle(Model $model)
    {
        // ...
    }
}
