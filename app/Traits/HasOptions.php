<?php

namespace App\Traits;

use App\Models\Option;

trait HasOptions
{
    /**
     * Get an option by key
     */
    public static function option($key, $default = null)
    {
        return Option::getByKey($key) ?? $default;
    }

    /**
     * Get options by group
     */
    public static function optionsByGroup($group)
    {
        return Option::getByGroup($group);
    }

    /**
     * Get all options grouped
     */
    public static function allOptions()
    {
        return Option::all();
    }

    /**
     * Check if option value is truthy
     */
    public static function optionIsTrue($key)
    {
        $value = Option::getByKey($key);
        return in_array($value, ['true', '1', 'yes', 'on']);
    }

    /**
     * Check if option value is falsy
     */
    public static function optionIsFalse($key)
    {
        return !self::optionIsTrue($key);
    }
}
