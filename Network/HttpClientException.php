<?php namespace PetrKnap\Utils\Network;

class HttpClientException extends \Exception
{
    const
        GenericException = 1,
        AccessException = 2;
}