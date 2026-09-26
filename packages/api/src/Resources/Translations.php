<?php

namespace Lunar\Api\Resources;

/**
 * How translatable attributes serialise: the storefront resolves one locale,
 * the admin surface returns the whole locale map it also accepts on write.
 */
enum Translations
{
    case Resolved;
    case Map;
}
