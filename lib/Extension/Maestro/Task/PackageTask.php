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

    public function __construct(string $name, bool $purgeWorkspace = false)
    {
        $this->name = $name;
        $this->purgeWorkspace = $purgeWorkspace;
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
}
