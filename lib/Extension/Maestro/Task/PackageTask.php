<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Task\Task;

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
    private $artifacts;

    public function __construct(string $name, bool $purgeWorkspace = false, array $artifacts = [])
    {
        $this->name = $name;
        $this->purgeWorkspace = $purgeWorkspace;
        $this->artifacts = $artifacts;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->name;
    }

    public function purgeWorkspace(): bool
    {
        return $this->purgeWorkspace;
    }

    public function artifacts(): array
    {
        return $this->artifacts;
    }
}
