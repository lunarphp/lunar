<?php

namespace Lunar\Validation;

use Illuminate\Support\MessageBag;
use Lunar\Exceptions\Carts\CartException;

abstract class BaseValidator
{
    /**
     * The parameters used in the validation
     */
    protected array $parameters;

    /**
     * Set the parameters to use for validation
     *
     * @param  array  $args
     */
    public function using(...$args): self
    {
        $this->parameters = $args;

        return $this;
    }

    /**
     * Fail the validation
     *
     * @param  string  $where
     * @param  string  $reason
     */
    public function fail($where, $reason): bool
    {
        $messages = new MessageBag(
            is_array($reason) ? $reason : [
                $where => $reason,
            ]
        );

        throw_if(
            $messages->isNotEmpty(),
            CartException::class,
            $messages
        );

        return false;
    }

    /**
     * Pass the validation
     */
    public function pass(): bool
    {
        return true;
    }

    /**
     * Validate against the passed parameters.
     */
    abstract public function validate(): bool;
}
