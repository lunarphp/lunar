<?php

namespace Lunar\Core\Enums;

/**
 * The presentation type of a product option, driving how its values render and
 * what payload each value carries:
 *
 *   - Text   — value name only (the default).
 *   - Colour — each value carries a hex colour in its `meta->colour`.
 *   - Swatch — each value carries a single image in its `images` media collection.
 *
 * The column backing this is a plain string, so a value the current admin does
 * not recognise round-trips untouched; callers resolve with `tryFrom()` and
 * treat `null` as "unknown, render the fallback".
 */
enum ProductOptionType: string
{
    case Text = 'text';
    case Colour = 'colour';
    case Swatch = 'swatch';

    /** Icon name shared with the panel so both surfaces label the type alike. */
    public function icon(): string
    {
        return match ($this) {
            self::Text => 'type',
            self::Colour => 'palette',
            self::Swatch => 'image',
        };
    }
}
