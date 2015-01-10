<?php namespace PetrKnap\Utils\Network;

/**
 * Simple PHP class that provides methods for sending HTTP requests
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2015-01-10
 * @category Network
 * @package  PetrKnap\Utils\Network
 * @version  0.1
 * @license  https://github.com/petrknap/utils/blob/master/LICENSE MIT
 */
class HttpClient
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $connectionTimeout = 60;

    /**
     * @var resource
     */
    private $curlHandle;

    /**
     * @var mixed
     */
    private $content;

    /**
     * Creates new instance
     */
    public function __construct() {
        $this->curlHandle = curl_init();
        if($this->curlHandle === false) {
            throw new HttpClientException("Couldn't create cURL handle.", HttpClientException::GenericException);
        }
    }

    /**
     * Sets URL
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Returns URL
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
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
     * Process request
     *
     * @param string|null $url URL
     * @return $this
     * @throws HttpClientException
     */
    public function request($url = null) {
        if(!$url) $url = $this->url;

        $status = true;

        $status = ($status && curl_setopt($this->curlHandle, CURLOPT_URL, $url));
        $status = ($status && curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout));
        $status = ($status && curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true));

        if($status === false) {
            throw new HttpClientException("Couldn't prepare request.", HttpClientException::GenericException);
        }

        try {
            $response = curl_exec($this->curlHandle);

            $errorNumber = curl_errno($this->curlHandle);
            if ($errorNumber) {
                throw new HttpClientException(curl_error($this->curlHandle), $errorNumber);
            }
            if (empty($response)) {
                throw new HttpClientException("Couldn't get remote content.", HttpClientException::AccessException);
            }

            $this->content = $response;
        }
        catch(HttpClientException $hce) {
            curl_close($this->curlHandle);
            $this->curlHandle = null;
            $this->content = null;
            throw $hce;
        }

        return $this;
    }
}