<?php namespace PetrKnap\Utils\DataStorage;

class DatabaseException extends \Exception
{
    const
        GenericException = 0,
        AccessException = 1,
        SecurityException = 2,
        PDOException = 3;
}