<?php

use PetrKnap\Utils\Network\HttpClient;
use PetrKnap\Utils\Network\HttpClientException;

class HttpClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HttpClient
     */
    private $client;

    public function setUp() {
        $this->client = new HttpClient();
    }

    public function testCanGetHomepage() {
        try {
            $this->client->request("http://petrknap.cz/");

            $this->assertNotNull($this->client->getContent());
        }
        catch (HttpClientException $hce) {
            $this->fail($hce->getMessage());
        }
    }

    public function testCanRecognizeWrongProtocol() {
        try {
            $this->client->request("wrong://petrknap.cz/");
            $this->fail("Wrong protocol doesn't throw exception.");
        }
        catch(HttpClientException $ignore) {
        }
    }
}