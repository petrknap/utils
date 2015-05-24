<?php

namespace PetrKnap\Utils\Apache;

/**
 * Simple HTAccess customizer
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2015-05-24
 * @category Apache
 * @package  PetrKnap\Utils\Apache
 * @version  0.1
 * @license  https://github.com/petrknap/utils/blob/master/LICENSE MIT
 */
class HTAccessCustomizer
{
    /**
     * @var string[]
     */
    private $constants;

    /**
     * @var string
     */
    private $pathToFile;

    /**
     * @var string
     */
    private $content;

    public function __construct(array $constants = array())
    {
        $this->constants = $constants;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function load($pathToFile)
    {
        $this->pathToFile = $pathToFile;

        $this->content = file_get_contents($pathToFile);

        return $this;
    }

    public function customize(array $constants = array())
    {
        $constants = array_merge($this->constants, $constants);

        foreach($constants as $name => $value) {
            $this->content = str_replace(
                sprintf("{__%s__}", $name),
                $value,
                $this->content
            );
        }

        return $this;
    }

    public function save($pathToFile = null)
    {
        if($pathToFile === null) {
            $pathToFile = $this->pathToFile;
        }

        file_put_contents($pathToFile, $this->content);

        return $this;
    }
}
