<?php
namespace vcms\resources;

use Exception;
use Throwable;

class ResourceException extends Exception
{
    const MISSING_ARGUMENTS = 1;
    const RESOURCE_NOT_FOUND = 2;

    public function __construct ($message="", $code=0, Throwable $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }

}