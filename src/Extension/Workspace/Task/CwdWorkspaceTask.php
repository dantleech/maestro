<?php

namespace Maestro\Extension\Workspace\Task;

use Maestro\Library\Task\Task;

class CwdWorkspaceTask implements Task
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return sprintf('spawning cwd workspace "%s" downstream', $this->name);
    }
}
