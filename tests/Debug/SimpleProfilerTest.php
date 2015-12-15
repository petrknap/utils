<?php

use PetrKnap\Utils\Debug\SimpleProfiler;

class SimpleProfilerTest extends PHPUnit_Framework_TestCase
{
    const ACCEPTABLE_DELAY = 0.002; // 2 ms

    public function setUp()
    {
        parent::setUp();

        SimpleProfiler::enable();
    }

    private function checkResult(array $result, $startLabel, $finishLabel)
    {
        $this->assertArrayHasKey(SimpleProfiler::START_LABEL, $result);
        $this->assertArrayHasKey(SimpleProfiler::START_TIME, $result);
        $this->assertArrayHasKey(SimpleProfiler::FINISH_LABEL, $result);
        $this->assertArrayHasKey(SimpleProfiler::FINISH_TIME, $result);
        $this->assertArrayHasKey(SimpleProfiler::ABSOLUTE_DURATION, $result);
        $this->assertArrayHasKey(SimpleProfiler::DURATION, $result);

        $this->assertLessThanOrEqual($result[SimpleProfiler::ABSOLUTE_DURATION], $result[SimpleProfiler::DURATION]);

        $this->assertEquals($startLabel, $result[SimpleProfiler::START_LABEL]);
        $this->assertEquals($finishLabel, $result[SimpleProfiler::FINISH_LABEL]);
    }

    public function testEmptyStack()
    {
        $this->setExpectedException(get_class(new \OutOfRangeException()));

        SimpleProfiler::finish();
    }

    public function testEnable()
    {
        SimpleProfiler::enable();

        $this->assertTrue(SimpleProfiler::start());
        $this->assertTrue(is_array(SimpleProfiler::finish()));
    }

    public function testDisable()
    {
        SimpleProfiler::disable();

        $this->assertFalse(SimpleProfiler::start());
        $this->assertFalse(SimpleProfiler::finish());
    }

    public function testOneLevelProfiling()
    {
        SimpleProfiler::start("start");

        $result = SimpleProfiler::finish("finish");

        $this->checkResult($result, "start", "finish");
    }

    public function testTwoLevelProfiling()
    {
        #region First level
        SimpleProfiler::start("L1_S");

        #region Second level A
        SimpleProfiler::start("L2A_S");

        $result = SimpleProfiler::finish("L2A_F");

        $this->checkResult($result, "L2A_S", "L2A_F");
        #endregion

        #region Second level B
        SimpleProfiler::start("L2B_S");

        $result = SimpleProfiler::finish("L2B_F");

        $this->checkResult($result, "L2B_S", "L2B_F");
        #endregion

        $result = SimpleProfiler::finish("L1_F");

        $this->checkResult($result, "L1_S", "L1_F");
        #endregion
    }

    public function testProfiling()
    {
        #region First level
        SimpleProfiler::start();

        sleep(1);

        #region Second level
        SimpleProfiler::start();

        sleep(2);

        $SLR = SimpleProfiler::finish();
        #endregion

        $FLR = SimpleProfiler::finish();
        #endregion

        $this->assertEquals(3, $FLR[SimpleProfiler::ABSOLUTE_DURATION], "", self::ACCEPTABLE_DELAY);
        $this->assertEquals(2, $SLR[SimpleProfiler::ABSOLUTE_DURATION], "",  self::ACCEPTABLE_DELAY);
        $this->assertEquals(1, $FLR[SimpleProfiler::DURATION], "", self::ACCEPTABLE_DELAY);
        $this->assertEquals(2, $SLR[SimpleProfiler::DURATION], "", self::ACCEPTABLE_DELAY);
    }
}
