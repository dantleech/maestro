<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Library\Task\Task;

class PackageInitTask implements Task
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
    private $env;
    /**
     * @var array
     */
    private $vars;
    /**
     * @var string|null
     */
    private $version;

    public function __construct(
        string $name,
        bool $purgeWorkspace,
        array $env,
        array $vars,
        ?string $version = null
    ) {
        $this->name = $name;
        $this->purgeWorkspace = $purgeWorkspace;
        $this->env = $env;
        $this->vars = $vars;
        $this->version = $version;
    }
    public function description(): string
    {
        return sprintf('initializing package');
    }

    public function env(): array
    {
        return $this->env;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function purgeWorkspace(): bool
    {
        return $this->purgeWorkspace;
    }

    public function vars(): array
    {
        return $this->vars;
    }

    public function version(): ?string
    {
        return $this->version;
    }
}
