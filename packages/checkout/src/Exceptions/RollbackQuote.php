<?php

namespace Lunar\Checkout\Exceptions;

use RuntimeException;

/**
 * Thrown inside the quote transaction purely to force a rollback: the quote
 * endpoint runs the real driver writes for one code path with the store
 * routes, but must persist nothing.
 */
class RollbackQuote extends RuntimeException {}
