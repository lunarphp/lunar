<?php

namespace Lunar\Exceptions\Carts;

use Exception;
use Illuminate\Contracts\Support\MessageBag;

class CartException extends Exception
{
    /**
     * The cart exception message bag
     */
    protected MessageBag $messageBag;

    public function __construct(MessageBag $messageBag = null)
    {
        parent::__construct(static::summarize($messageBag));
        $this->messageBag = $messageBag;
    }

    /**
     * Create an error message summary from the validation errors.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return string
     */
    protected static function summarize($messageBag)
    {
        $messages = $messageBag->all();

        if (! count($messages) || ! is_string($messages[0])) {
            return 'The cart action was invalid';
        }

        $message = array_shift($messages);

        if ($count = count($messages)) {
            $pluralized = $count === 1 ? 'error' : 'errors';

            $message .= ' '.__("(and :count more $pluralized)", compact('count'));
        }

        return $message;
    }

    public function errors()
    {
        return $this->messageBag;
    }
}
