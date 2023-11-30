<?php

namespace Lunar\Exceptions\Carts;

use Illuminate\Contracts\Support\MessageBag;
use Lunar\Exceptions\LunarException;

class CartException extends LunarException
{
    /**
     * The cart exception message bag.
     */
    protected MessageBag $messageBag;

    public function __construct(MessageBag $messageBag)
    {
        parent::__construct(static::summarize($messageBag));
        $this->messageBag = $messageBag;
    }

    /**
     * Create a summary from the error messages.
     */
    protected static function summarize(MessageBag $messageBag): string
    {
        $messages = $messageBag->all();

        if (! count($messages) || ! is_string($messages[0])) {
            return __('lunar::exceptions.carts.invalid_action');
        }

        $message = array_shift($messages);

        if ($count = count($messages)) {
            $pluralized = $count === 1 ? 'error' : 'errors';

            $message .= ' '.__("(and :count more $pluralized)", compact('count'));
        }

        return $message;
    }

    /**
     * Get the error message bag.
     */
    public function errors(): MessageBag
    {
        return $this->messageBag;
    }
}
