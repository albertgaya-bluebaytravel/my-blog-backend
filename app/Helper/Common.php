<?php

if (!function_exists('prefix_url')) {
    /**
     * Add prefix
     */
    function prefix_url(string $url): string
    {
        return url('api/' . config('app.version', 'v1') . '/' . ltrim($url, '/'));
    }
}
