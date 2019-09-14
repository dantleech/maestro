<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Library\Task\Task;

class InitTask implements Task
{
    /**
     * @var array
     */
    private $environment;

    /**
     * @var array
     */
    private $variables;

    public function __construct(array $environment, array $variables)
    {
        $this->environment = $environment;
        $this->variables = $variables;
    }

    public function description(): string
    {
        return 'initializing';
    }

    public function variables(): array
    {
        return $this->variables;
    }

    public function environment(): array
    {
        return $this->environment;
    }
}
