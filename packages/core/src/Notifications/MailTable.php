<?php

namespace Lunar\Core\Notifications;

/**
 * Markdown table helpers for the mail templates.
 */
class MailTable
{
    /**
     * A value safe inside a markdown table cell: a `|` would start a new cell
     * and a newline would end the row.
     */
    public static function cell(?string $value): string
    {
        return str_replace(['|', "\r\n", "\n", "\r"], ['\|', ' ', ' ', ' '], (string) $value);
    }
}
