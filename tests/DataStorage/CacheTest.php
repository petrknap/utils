<?php

use PetrKnap\Utils\DataStorage\Cache;
use PetrKnap\Utils\DataStorage\Database;

class CacheTest extends PHPUnit_Framework_TestCase
{
    private $database;

    const CACHE_KEY = "content", CACHE_PREFIX_A = "A", CACHE_PREFIX_B = "B";

    public function setUp() {
        $this->database = new Database();
        $this->database->Type = Database::TYPE_SQLite;
        $this->database->HostOrPath = ":memory:";
        $this->database->CharacterSet = "UTF8";
    }

    public function testSimpleCachingWorks() {
        $count = 0;
        $counter = function() use (&$count) {
            return ++$count;
        };

        $cache = new Cache($this->database);

        $this->assertFalse($cache->get(self::CACHE_KEY));

        for($i = 0; $i < 100; $i++) {
            $content = $cache->get(self::CACHE_KEY);
            if(!$content) {
                $content = $counter();
                $cache->add(self::CACHE_KEY, $content);
            }
            $this->assertEquals(1, $content);
        }
    }

    public function testDebugModeWorks() {
        $cache = new Cache($this->database);

        $cache->DebugMode = true;
        $cache->add(self::CACHE_KEY, "debug");

        $this->assertFalse($cache->get(self::CACHE_KEY));
    }

    public function testItemCanBeDeletedFromCacheByUser() {
        $cache = new Cache($this->database);
        $cache->add(self::CACHE_KEY, "remove me");

        $this->assertEquals("remove me", $cache->get(self::CACHE_KEY));

        $cache->del(self::CACHE_KEY);

        $this->assertFalse($cache->get(self::CACHE_KEY));
    }

    public function testItemCanByDeletedByTime() {
        $cache = new Cache($this->database);

        $content = "remove me after 1 second";

        $cache->add(self::CACHE_KEY, $content, 2);

        $this->assertEquals($content, $cache->get(self::CACHE_KEY));

        sleep(4);

        $this->assertFalse($cache->get(self::CACHE_KEY));
    }

    public function testPrefixesWorksFine() {
        $cache = new Cache($this->database);

        $cache->Prefix = self::CACHE_PREFIX_A;
        $cache->add(self::CACHE_KEY, "A");

        $cache->Prefix = self::CACHE_PREFIX_B;
        $this->assertFalse($cache->get(self::CACHE_KEY));
        $cache->add(self::CACHE_KEY, "B");

        $cache->Prefix = self::CACHE_PREFIX_A;
        $this->assertEquals("A", $cache->get(self::CACHE_KEY));

        $cache->Prefix = self::CACHE_PREFIX_B;
        $this->assertEquals("B", $cache->get(self::CACHE_KEY));
    }

    public function testTwoInstancesOnOneDatabaseWorksAsOneInstance() {
        $cacheA = new Cache($this->database);
        $cacheB = new Cache($this->database);

        $content = "content";
        $cacheA->add(self::CACHE_KEY, $content);
        $this->assertSame($content, $cacheB->get(self::CACHE_KEY));
    }

}