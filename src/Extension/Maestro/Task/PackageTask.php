<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Node\Task;

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
    private $environment;

    public function __construct(string $name, bool $purgeWorkspace = false, array $environment = [])
    {
        $this->name = $name;
        $this->purgeWorkspace = $purgeWorkspace;
        $this->environment = $environment;
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

    public function environment(): array
    {
        return $this->environment;
    }
}
