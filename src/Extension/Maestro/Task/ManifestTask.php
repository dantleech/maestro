<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Node\Task;

class ManifestTask implements Task
{
    /**
     * @var string|null
     */
    private $path;

    /**
     * @var array
     */
    private $vars;

    /**
     * @var array
     */
    private $env;

    public function __construct(?string $path, array $vars = [], array $env = [])
    {
        $this->path = $path;
        $this->vars = $vars;
        $this->env = $env;
    }

    public function description(): string
    {
        return 'initalizing manifest';
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function env(): array
    {
        return $this->env;
    }

    public function vars(): array
    {
        return $this->vars;
    }
}
