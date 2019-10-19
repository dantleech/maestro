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
     * @var array
     */
    private $vars;

    /**
     * @var string
     */
    private $path;

    public function __construct(array $env = [], array $vars = [], string $path)
    {
        $this->env = $env;
        $this->vars = $vars;
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

    public function vars(): array
    {
        return $this->vars;
    }
}
