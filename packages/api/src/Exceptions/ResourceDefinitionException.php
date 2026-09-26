<?php

namespace Lunar\Api\Exceptions;

use LogicException;

/** A resource or extension is mis-registered; raised at boot, not per request. */
class ResourceDefinitionException extends LogicException {}
