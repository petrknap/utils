<?php

namespace PetrKnap\Utils\Debug;

/**
 * Simple PHP class for profiling
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2015-12-13
 * @category Debug
 * @package  PetrKnap\Utils\Debug
 * @version  0.1
 * @license  https://github.com/petrknap/utils/blob/master/LICENSE MIT
 */
class SimpleProfiler
{
    #region Result keys
    const START_LABEL = "start_label"; // string
    const START_TIME = "start_time"; // float start time in seconds
    const FINISH_LABEL = "finish_label"; // string
    const FINISH_TIME = "finish_time"; // float finish time in seconds
    const ABSOLUTE_DURATION = "absolute_duration"; // float absolute duration in seconds
    const DURATION = "duration"; // float duration in seconds
    #endregion

    private static $offset = 0;

    private static $stack = [];

    /**
     * Start profiling
     *
     * @param string $label
     */
    public static function start($label = null)
    {
        array_push(self::$stack, [
            self::START_LABEL => $label,
            self::START_TIME => microtime(true)
        ]);
    }

    /**
     * Finish profiling and get result
     *
     * @param string $label
     * @return array result
     */
    public static function finish($label = null)
    {
        $now = microtime(true);

        if (empty(self::$stack)) {
            throw new \OutOfRangeException("Call " . __CLASS__ . "::start() first.");
        }

        $result = array_pop(self::$stack);

        $result[self::FINISH_LABEL] = $label;
        $result[self::FINISH_TIME] = $now;
        $result[self::ABSOLUTE_DURATION] = $result[self::FINISH_TIME] - $result[self::START_TIME];
        $result[self::DURATION] = $result[self::ABSOLUTE_DURATION] - self::$offset;

        self::$offset = $result[self::ABSOLUTE_DURATION];

        if (empty(self::$stack)) {
            self::$offset = 0;
        }

        return $result;
    }
}
