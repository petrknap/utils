<?php

use PetrKnap\Utils\Apache\HTAccessCustomizer;

class HTAccessCustomizerTest extends PHPUnit_Framework_TestCase
{
    const HTAccessContent = <<<HTAccessContent
# Last modification: {__NOW__}

ErrorDocument 404 {__DIR__}/error_404.html

RewriteEngine On

RewriteRule ^(.*)$ {__DIR__}/index.php?uri=$1
HTAccessContent;

    private $pathToFile;

    private $constants;

    public function setUp()
    {
        $this->pathToFile = __DIR__ . "/.htaccess";

        file_put_contents($this->pathToFile, self::HTAccessContent);

        $now = new DateTime("now");

        $this->constants = array(
            "NOW" => $now->format(DateTime::ISO8601),
            "DIR" => __DIR__
        );
    }

    public function tearDown()
    {
        unlink($this->pathToFile);
    }

    public function testCanLoad()
    {
        $customizer = new HTAccessCustomizer();

        $this->assertEquals(self::HTAccessContent, $customizer->load($this->pathToFile)->getContent());
    }

    public function testCanCustomize()
    {
        $customizer = new HTAccessCustomizer();

        $actual = $customizer->setContent(self::HTAccessContent)->customize($this->constants)->getContent();

        $expected = self::HTAccessContent;
        foreach($this->constants as $name => $value) {
            $expected = str_replace("{__{$name}__}", $value, $expected);
        }

        $this->assertEquals($expected, $actual);
    }

    public function testCanSave()
    {
        $customizer = new HTAccessCustomizer();

        $pathToFile = "{$this->pathToFile}2";

        $customizer->load($this->pathToFile)->save($pathToFile);

        $this->assertFileEquals($this->pathToFile, $pathToFile);

        unlink($pathToFile);
    }
}
