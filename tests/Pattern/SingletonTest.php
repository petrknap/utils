<?php

use PetrKnap\Utils\Pattern\Singleton;

class SingletonTest extends PHPUnit_Framework_TestCase
{
    public function testReturnsValidInstance()
    {
        $instance = SingletonTestMock::getInstance();

        $this->assertInstanceOf(SingletonTestMock::getClassName(), $instance);
    }

    public function testReturnsOnlyOneInstance()
    {
        $data = __METHOD__;

        /** @var SingletonTestMock $a */
        $a = SingletonTestMock::getInstance();

        $a->setData($data);

        /** @var SingletonTestMock $b */
        $b = SingletonTestMock::getInstance();

        $this->assertEquals($data, $b->getData());
    }
}

class SingletonTestMock extends Singleton
{
    private $data;

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public static function getClassName()
    {
        return __CLASS__;
    }
}
