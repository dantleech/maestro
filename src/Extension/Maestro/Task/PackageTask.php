<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Graph\Task;

class PackageTask implements Task
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $purgeWorkspace;

    /**
     * @var array
     */
    private $vars;

    /**
     * @var array
     */
    private $env;

    public function __construct(
        string $name,
        bool $purgeWorkspace = false,
        array $vars = [],
        array $env = []
    ) {
        $this->name = $name;
        $this->purgeWorkspace = $purgeWorkspace;
        $this->vars = $vars;
        $this->env = $env;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return sprintf('initializing %s', $this->name);
    }

    public function purgeWorkspace(): bool
    {
        return $this->purgeWorkspace;
    }

    public function vars(): array
    {
        return $this->vars;
    }

    public function env(): array
    {
        return $this->env;
    }
}
