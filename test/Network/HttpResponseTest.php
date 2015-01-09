<?php

use PetrKnap\Utils\Network\HttpResponse;

require_once(__DIR__ . "/../../Network/HttpResponse.php");

class HttpResponseTest extends PHPUnit_Framework_TestCase
{
    public function testRedirectionWorks() {
        $r1 = new HttpResponse();
        $r1->redirect("http://dev.petrknap.cz/");

        $this->assertArrayHasKey("Location", $r1->getHeaders());
        $this->assertArrayNotHasKey("HTTP/1.1 301 Moved Permanently", $r1->getHeaders());

        $r2 = new HttpResponse();
        $r2->redirect("http://dev.petrknap.cz/", true);

        $this->assertArrayHasKey("Location", $r2->getHeaders());
        $this->assertArrayHasKey("HTTP/1.1 301 Moved Permanently", $r2->getHeaders());
    }
}