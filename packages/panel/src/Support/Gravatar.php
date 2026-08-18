<?php

namespace Lunar\Panel\Support;

class Gravatar
{
    /**
     * Gravatar URL for an email. `d=404` so a missing Gravatar 404s and the
     * frontend can fall back to an initials avatar rather than a placeholder.
     */
    public static function url(?string $email, int $size = 64): ?string
    {
        if (! $email) {
            return null;
        }

        return 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($email)))."?d=404&s={$size}";
    }
}
