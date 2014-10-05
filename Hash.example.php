<?php

require_once("./Hash.class.php");

use PetrKnap\Utils\Security\Hash;

header("Content-Type: text/plain; charset=utf-8");
$p = array("text" => "Hello world!");
$p["MD5"] = Hash::B64MD5($p["text"]);
$p["SHA-1"] = Hash::B64SHA1($p["text"]);
$p["SHA-256"] = Hash::B64SHA256($p["text"]);
$p["SHA-384"] = Hash::B64SHA384($p["text"]);
$p["SHA-512"] = Hash::B64SHA512($p["text"]);
$p["RandomBytes"] = Hash::RandomBytes(7);
print_r($p);
print("\nLength of MD5 as B64 is " . Hash::B64MD5length . " characters.");

/* Output:

Array
(
    [text] => Hello world!
    [MD5] => hvsmnRkNLIX24EaM7KQqIA
    [SHA-1] => 00hq6RNueFa8QiEjhep5cJRHWAI
    [SHA-256] => wFNeS+K3n/2TKRMFQ2v4iTFOSj+uwF7P/Lt98xrZ5Ro
    [SHA-384] => hiVfosNuSzCWnq4X3DTHcsvr38WLWEA5AL6HYU6xo0uHgCY/JV615lypu7hkHMz+
    [SHA-512] => 9s3ioPgZMUzd5V/CJ9jX2uPSjMVWIioKitZtkcytSq1glPUXohgjYMmqz2o9wyMWLLb9jN/+2w/gOPVehf+1tg
    [RandomBytes] => A123BEE3433E59
)

Length of MD5 as B64 is 22 characters.

*/