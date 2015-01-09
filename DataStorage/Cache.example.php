<?php

require_once("Database.class.php");
require_once("Cache.class.php");
use PetrKnap\IndependentClass\Cache;
use PetrKnap\IndependentClass\Database;

header("Content-Type: text/plain; charset=utf-8");

$dbCache = new Database();
$dbCache->Type = $dbCache::TYPE_SQLite;
$dbCache->HostOrPath = ":memory:";
$dbCache->CharacterSet = "UTF8";

$cache = new Cache($dbCache);

$cache->add("test", "example\n", 1);
print_r($cache->get("test"));

$cache->add("test", "EXAMPLE\n", 1);
print_r($cache->get("test"));

sleep(2); // let's expire

$cache->add("test", "EXAMPLE\n", 1);
print_r($cache->get("test"));

$cache->add("test", "example\n", 1);
print_r($cache->get("test"));

$cache->del("test"); // delete key

$cache->add("test", "example\n", 1);
print_r($cache->get("test"));

unset($cache);