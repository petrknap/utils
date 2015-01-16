<?php

use PetrKnap\Utils\Network\HttpResponse;

class HttpResponseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HttpResponse
     */
    private $response;

    public function setUp() {
        $this->response = new HttpResponse();
    }

    public function testTemporaryRedirectionWorks() {
        $this->response->redirect("http://dev.petrknap.cz/");

        $this->assertArrayHasKey("Location", $this->response->getHeaders());
        $this->assertArrayNotHasKey("HTTP/1.1 301 Moved Permanently", $this->response->getHeaders());
    }

    public function testPermanentRedirectionWorks() {
        $this->response->redirect("http://dev.petrknap.cz/");

        $this->assertArrayHasKey("Location", $this->response->getHeaders());
        $this->assertArrayNotHasKey("HTTP/1.1 301 Moved Permanently", $this->response->getHeaders());
    }
}