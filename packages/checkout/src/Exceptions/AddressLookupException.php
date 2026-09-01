<?php

namespace Lunar\Checkout\Exceptions;

use Exception;

/**
 * A lookup vendor failed. The transport layer turns this into a generic
 * message: the vendor's own response body never reaches the browser.
 */
class AddressLookupException extends Exception {}
