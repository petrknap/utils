<?php

namespace PetrKnap\Utils\DataStorage;

class FileException extends \Exception
{
    const
        GenericException = 0,
        AccessException = 1,
        FileExistsException = 2,
        FileNotFoundException = 404;
}
