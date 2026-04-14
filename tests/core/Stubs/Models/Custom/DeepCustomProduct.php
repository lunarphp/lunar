<?php

namespace Lunar\Tests\Core\Stubs\Models\Custom;

/**
 * Multi-level inheritance test model.
 *
 * This model extends CustomProduct which already extends \Lunar\Models\Product.
 * This creates a 3-level inheritance chain to test the table prefix bug fix.
 */
class DeepCustomProduct extends CustomProduct {}
