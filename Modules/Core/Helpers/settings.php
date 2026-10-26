<?php

use Modules\Core\Entities\Setting;

if (!function_exists('settings')) {
    /**
     * Get or set a setting value
     *
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    function settings(string|array $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Setting::set($k, $v);
            }
            return null;
        }

        return Setting::get($key, $default);
    }
}
