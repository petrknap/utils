<?php namespace PetrKnap\Utils\Network;

/**
 * Simple PHP class that provides methods for preparing HTTP responses
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2015-01-09
 * @category Security
 * @package  PetrKnap\Utils\Network
 * @version  0.2
 * @license  https://github.com/petrknap/utils/blob/master/LICENSE MIT
 * @homepage http://dev.petrknap.cz/HttpResponse.class.php.html
 *
 * @change 0.2 Void returning methods now returns $this
 */
class HttpResponse
{
    /**
     * @var string[] Headers
     */
    private $headers;

    /**
     * @var mixed Content
     */
    private $content;

    /**
     * Creates new instance
     */
    public function __construct() {
        $this->headers = array();
        $this->content = null;
    }

    /**
     * Sets header into headers collection
     *
     * @param string $header
     * @return $this
     */
    public function setHeader($header) {
        $haystack = explode(":", $header);
        $this->headers[$haystack[0]] = $header;

        return $this;
    }

    /**
     * Returns headers collection
     *
     * @return string[]
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Sets content
     *
     * @param mixed $content
     * @return $this
     */
    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Returns content
     *
     * @return mixed
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Sends HTTP response to client
     */
    public function send() {
        foreach($this->getHeaders() as $header) {
            header($header);
        }
        print($this->getContent());
    }

    /**
     * Creates HTTP response for redirect to another URL
     * @param string $url Target URL
     * @param bool $movedPermanently True if redirection is permanent
     * @return $this
     */
    public function redirect($url, $movedPermanently = false)
    {
        $title = "Page was moved";
        if ($movedPermanently) {
            $this->setHeader("HTTP/1.1 301 Moved Permanently");
            $title = "{$title} permanently";
        }
        $this->setHeader("Location: {$url}");
        $this->setContent("<!DOCTYPE html>
<html>
    <head>
        <title>{$title}</title>
        <meta http-equiv='refresh' content='5;url={$url}'>
    </head>
    <body>
    <h1>{$title}</h1>
    <script type='text/javascript'>
        //<![CDATA[
            window.location.replace('{$url}');
            window.location.href = '{$url}';
        //]]>
    </script>
    <p>{$title} to <a href='{$url}'>{$url}</a>.</p>
</body>
</html>");
        return $this;
    }
}