<?php

namespace App\Traits;

use App\Repositories\OptionRepo;

trait HasOptions
{
    protected static OptionRepo $optionRepo;

    /**
     * Initialize the option repo
     */
    protected static function initOptionRepo(): OptionRepo
    {
        if (!isset(self::$optionRepo)) {
            self::$optionRepo = app(OptionRepo::class);
        }
        return self::$optionRepo;
    }

    /**
     * Get an option by key
     */
    public static function option($key, $default = null)
    {
        return self::initOptionRepo()->getByKey($key) ?? $default;
    }

    /**
     * Get options by group
     */
    public static function optionsByGroup($group)
    {
        return self::initOptionRepo()->getByGroup($group);
    }

    /**
     * Get all options grouped
     */
    public static function allOptions()
    {
        return app(OptionRepo::class)->get();
    }

    /**
     * Check if option value is truthy
     */
    public static function optionIsTrue($key)
    {
        $value = self::initOptionRepo()->getByKey($key);
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
