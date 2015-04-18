<?php

namespace PetrKnap\Utils\Pattern;

/**
 * Singleton pattern
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2015-04-18
 * @category Patterns
 * @package  PetrKnap\Utils\Pattern
 * @version  0.1
 * @license  https://github.com/petrknap/utils/blob/master/LICENSE MIT
 */
abstract class Singleton
{
    /**
     * @var self[]
     */
    private static $instances = array();

    /**
     * Creates new instance
     */
    private function __construct()
    {
        // Constructor must be private
    }

    /**
     * Returns instance, if instance does not exist then creates new one and returns it
     *
     * @return self
     */
    public static function getInstance()
    {
        $self = get_called_class();
        if (!isset(self::$instances[$self])) {
            self::$instances[$self] = new $self;
        }
        return self::$instances[$self];
    }
}
