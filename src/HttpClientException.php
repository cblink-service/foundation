<?php

namespace Cblink\Service\Foundation;

use InvalidArgumentException;

class HttpClientException extends InvalidArgumentException
{
    public $code = 400;

    public function __construct($message = "", $code = 400)
    {
        parent::__construct($message, $code);
    }

}