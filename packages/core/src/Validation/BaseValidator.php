<?php

namespace Lunar\Validation;

use Illuminate\Support\MessageBag;
use Lunar\Exceptions\Carts\CartException;

abstract class BaseValidator
{
    /**
     * The parameters used in the validation
     *
     * @var array
     */
    protected array $parameters;

    /**
     * Set the parameters to use for validation
     *
     * @param Array $args
     *
     * @return self
     */
    public function using(...$args): self
    {
        $this->parameters = $args;

        return $this;
    }

    /**
     * Fail the validation
     *
     * @param string $where
     * @param string $reason
     *
     * @return bool
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
     *
     * @return bool
     */
    public function pass(): bool
    {
        return true;
    }

    /**
     * Validate against the passed parameters.
     *
     * @return boolean
     */
    abstract public function validate(): bool;
}
