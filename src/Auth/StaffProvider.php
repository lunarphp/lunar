<?php

namespace GetCandy\Hub\Auth;

use GetCandy\Hub\Models\Staff;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class StaffProvider extends EloquentUserProvider
{
    /**
     * Create a new database user provider.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param string                               $model
     *
     * @return void
     */
    public function __construct(HasherContract $hasher)
    {
        $this->model = Staff::class;
        $this->hasher = $hasher;
    }
}
