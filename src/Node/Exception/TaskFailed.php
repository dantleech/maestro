<?php

namespace Maestro\Node\Exception;

use Maestro\Node\Environment;
use RuntimeException;

class TaskFailed extends RuntimeException
{
    /**
     * @var Environment
     */
    private $environment;

    public function __construct(string $message, Environment $environment = null)
    {
        $this->environment = $environment ?: Environment::create();
        parent::__construct($message);
        $this->message = $message;
    }

    public function environment(): Environment
    {
        return $this->environment;
    }
}
