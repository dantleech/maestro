<?php

namespace Maestro\Node\Exception;

use Maestro\Node\Environment;
use RuntimeException;

class TaskFailed extends RuntimeException
{
    public function __construct(string $message, $code = 1)
    {
        parent::__construct($message, $code);
        $this->message = $message;
    }
}
