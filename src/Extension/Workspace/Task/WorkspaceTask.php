<?php

namespace Maestro\Extension\Workspace\Task;

use Maestro\Library\Task\Task;

class WorkspaceTask implements Task
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function description(): string
    {
        return sprintf('creating simple workspace "%s"', $this->name);
    }

    public function name(): string
    {
        return $this->name;
    }
}
