<?php

namespace Lunar\Licensing;

class Utils
{
    public static function getDomain($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if ($path && ! $host) {
            $host = $path;
        }

        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            // IP address returned as domain
            return $host; //* or replace with null if you don't want an IP back
        }

        $domain_array = explode('.', str_replace('www.', '', $host));

        $count = count($domain_array);

        if ($count >= 3 && strlen($domain_array[$count - 2]) == 2) {
            // SLD (example.co.uk)
            return implode('.', array_splice($domain_array, $count - 3, 3));
        } elseif ($count >= 2) {
            // TLD (example.com)
            return implode('.', array_splice($domain_array, $count - 2, 2));
        }
    }
}
