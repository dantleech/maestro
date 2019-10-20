<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Library\Task\Task;

class PackageTask implements Task
{
    /**
     * @var string|null
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

    /**
     * @var string|null
     */
    private $url;

    public function __construct(
        ?string $name = null,
        bool $purgeWorkspace = false,
        ?string $url = null,
        array $env = [],
        array $vars = [],
        ?string $version = null
    ) {
        $this->name = $name;
        $this->purgeWorkspace = $purgeWorkspace;
        $this->env = $env;
        $this->vars = $vars;
        $this->version = $version;
        $this->url = $url;
    }
    public function description(): string
    {
        return sprintf('initializing package %s', $this->name);
    }

    public function env(): array
    {
        return $this->env;
    }

    public function name(): ?string
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

    public function url(): ?string
    {
        return $this->url;
    }
}
