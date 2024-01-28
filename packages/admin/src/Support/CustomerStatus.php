<?php

namespace Lunar\Admin\Support;

class CustomerStatus
{
    protected static array $cachedStatusColor = [];

    protected static array $cachedStatusLabel = [];

    protected static array $cachedStatusIcon = [];

    public static function getLabel($status): string
    {
        return static::$cachedStatusLabel[$status] ??= $status ? __('lunarpanel::customer.table.new.label') : __('lunarpanel::customer.table.returning.label');
    }

    public static function getColor($status): string
    {
        return static::$cachedStatusColor[$status] ??= $status ? 'success' : 'gray';
    }

    public static function getIcon($status): string
    {
        return static::$cachedStatusIcon[$status] ??= $status ? 'heroicon-m-sparkles' : 'heroicon-m-arrow-path';
    }
}
