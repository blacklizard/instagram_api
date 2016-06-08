<?php

namespace Jeevan\Exception;

class InstagramException extends \Exception
{
    /**
     * InstagramException constructor.
     */
    public function __construct($message = null)
    {
        parent::__construct($message);
    }
}