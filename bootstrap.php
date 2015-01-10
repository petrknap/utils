<?php

function class_auto_loader($className)
{
    $parts = explode("\\", $className);

    array_shift($parts); // remove PetrKnap
    array_shift($parts); // remove Utils

    $path = __DIR__ . '/' . implode('/', $parts) . '.php';

    if(file_exists($path)) {
        /** @noinspection PhpIncludeInspection */
        require_once($path);
    }
}

spl_autoload_register('class_auto_loader');