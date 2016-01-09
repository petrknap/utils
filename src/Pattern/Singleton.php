<?php

namespace PetrKnap\Utils\Pattern;

use PetrKnap\Php\Singleton\SingletonInterface;
use PetrKnap\Php\Singleton\SingletonTrait;

/**
 * Singleton pattern
 *
 * @author     Petr Knap <dev@petrknap.cz>
 * @since      2015-04-18
 * @category   Patterns
 * @package    PetrKnap\Utils\Pattern
 * @version    0.1
 * @license    https://github.com/petrknap/utils/blob/master/LICENSE MIT
 * @deprecated extracted to https://github.com/petrknap/php-singleton
 */
abstract class Singleton implements SingletonInterface
{
    use SingletonTrait;
}
