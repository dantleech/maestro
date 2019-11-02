<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Library\Task\Task;

class InitTask implements Task
{
    /**
     * @var array
     */
    private $env;

    /**
     * @var string
     */
    private $path;

    public function __construct(array $env = [], string $path)
    {
        $this->env = $env;
        $this->path = $path;
    }

    public function description(): string
    {
        return 'initializing';
    }

    public function env(): array
    {
        return $this->env;
    }

    public function path(): string
    {
        return $this->path;
    }
}
